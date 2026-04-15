<?php

namespace App\Filament\Resources\Positions\Schemas;

use App\Enums\DepartmentStatus;
use App\Enums\PositionStatus;
use App\Enums\PositionType;
use App\Filament\Schemas\StateCasts\ClinicalRadioStateCast;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Illuminate\Database\Eloquent\Builder;

class PositionForm
{
    public static function configure(Schema $schema, bool $withEmployee = false, ?Employee $employee = null): Schema
    {

        return $schema->components([

            Select::make('employee_id')
                ->relationship('employee', 'name')
                ->searchable(
                    ['name', 'surname', 'personal_number']
                )
                ->preload()
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' '.$record->surname)
                ->label(__('filament.employee_id'))
                ->disabled(function (?Position $record) {
                    return $record !== null;
                })
                ->dehydrated()
                ->required()
                ->columnSpanFull()
                ->hidden($withEmployee || $employee !== null),
            Section::make()
                ->schema([
                    TextEntry::make('employee.name')
                        ->label(__('filament.employee_id'))
                        ->formatStateUsing(function ($state, $record) {
                            return $record->employee?->name.' '.$record->employee?->surname;
                        })
                        ->size('lg')
                        ->disabled(fn (?Position $record): bool => $record !== null)
                        ->columnSpanFull(),
                ])
                ->visible($withEmployee)
                ->columnSpanFull(),
            Section::make()
                ->label(__('filament.vacation_days'))
                ->schema([
                    TextEntry::make('used_days_off_days')
                        ->label(__('filament.used_days_off_days')),
                    TextEntry::make('transferred_days')
                        ->label(__('filament.transferred_days')),
                    TextEntry::make('total_vacation_days')
                        ->label(__('filament.total_vacation_days')),
                    TextEntry::make('used_vacation_days')
                        ->label(__('filament.used_vacation_days')),

                    TextEntry::make('available_vacation_days')
                        ->label(__('filament.available_vacation_days'))
                        ->color(fn ($state) => $state <= 2 ? 'danger' : 'success'),
                ])
                ->visible($withEmployee)

                ->columns(5)
                ->columnSpanFull(),
            Tabs::make(__('filament.tabs.container'))
                ->tabs([
                    Tab::make(__('filament.tabs.basic_information'))
                        ->schema([

                            Select::make('department_id')
                                ->label(__('filament.department_id'))
                                ->relationship(
                                    'department',
                                    'name',
                                    fn (Builder $query) => $query->whereIn('status', [
                                        DepartmentStatus::ACTIVE,
                                        DepartmentStatus::ARCHIVED,
                                    ])
                                        ->orderBy('name'),
                                )
                                ->searchable()
                                ->preload()
                                ->rule(static function (Field $component) {
                                    return function (string $attribute, mixed $value, Closure $fail) use ($component): void {
                                        if (blank($value)) {
                                            return;
                                        }

                                        $department = Department::query()->find($value);

                                        if ($department === null) {
                                            return;
                                        }

                                        if ($department->status !== DepartmentStatus::ACTIVE) {
                                            return;
                                        }

                                        $vacancyLimit = max(0, (int) $department->vacancy_count);

                                        $ignorePositionId = $component->getRecord()?->getKey();

                                        $occupied = Position::query()
                                            ->where('department_id', $value)
                                            ->when(
                                                $ignorePositionId,
                                                fn (Builder $query): Builder => $query->whereKeyNot($ignorePositionId),
                                            )
                                            ->count();

                                        if ($occupied + 1 > $vacancyLimit) {
                                            $fail(__('filament.admin.position_resource.department_vacancy_limit', [
                                                'max' => $vacancyLimit,
                                            ]));
                                        }
                                    };
                                })
                                ->required()
                                ->columnSpanFull(),

                            Select::make('place_id')
                                ->label(__('filament.place_id'))
                                ->relationship('place', 'name', fn (Builder $query) => $query->where('is_active', true))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpanFull(),
                            Select::make('position_type')
                                ->label(__('filament.position_type'))
                                ->options(PositionType::class)
                                ->required()
                                ->live()
                                ->afterStateHydrated(function (Set $set, mixed $state): void {
                                    self::applyNonStaffTypeForPositionType($set, $state);
                                })
                                ->afterStateUpdated(function (Set $set, mixed $state): void {
                                    self::applyNonStaffTypeForPositionType($set, $state);
                                })
                                ->columns(1),
                            Radio::make('staff_type')
                                ->label(__('filament.staff_type'))
                                ->inline()
                                ->default(0)
                                ->options([
                                    '1' => 'საშტატო', // საშტატო
                                    '2' => 'არა საშტატო', // არა საშტატო
                                ])
                                ->columns(1)
                                ->required(),

                            DatePicker::make('date_start')
                                ->label(__('filament.date_start')),
                            DatePicker::make('date_end')
                                ->label(__('filament.date_end'))
                                ->required(function ($get): bool {
                                    if (! $get('status')) {
                                        return false;
                                    }
                                    $required = $get('status')->value == PositionStatus::Dismissal->value;

                                    return $required;
                                }),

                            TextInput::make('act_number')
                                ->label(__('filament.act_number')),
                            DatePicker::make('act_date')
                                ->label(__('filament.act_date')),

                            Select::make('status')
                                ->label(__('filament.status'))
                                ->options(PositionStatus::class)
                                ->required(),

                            Section::make()
                                ->schema([
                                    Radio::make('clinical')
                                        ->label(__('filament.clinical'))
                                        ->inline()
                                        ->options([
                                            '0' => __('filament.clinical_option.clinical'),
                                            '1' => __('filament.clinical_option.non_clinical'),
                                        ])
                                        ->stateCast(new ClinicalRadioStateCast)
                                        ->required(fn ($get): bool => self::positionTypeShowsClinical($get('position_type')))
                                        ->visible(fn ($get): bool => self::positionTypeShowsClinical($get('position_type'))),

                                    TextInput::make('clinical_text')
                                        ->label(__('filament.clinical_text'))
                                        ->visible(fn ($get): bool => self::positionTypeShowsClinical($get('position_type'))),
                                ])
                                ->columnSpanFull()
                                ->visible(fn ($get): bool => self::positionTypeShowsClinical($get('position_type'))),
                            Section::make()
                                ->schema([
                                    Toggle::make('automative_renewal')
                                        ->label(__('filament.automative_renewal'))
                                        ->visible(fn ($get): bool => self::positionTypeShowsAutomativeRenewal($get('position_type')))
                                        ->required(fn ($get): bool => self::positionTypeShowsAutomativeRenewal($get('position_type'))),
                                ])
                                ->columnSpanFull()
                                ->visible(fn ($get): bool => self::positionTypeShowsAutomativeRenewal($get('position_type'))),

                            TextInput::make('salary')
                                ->label(__('filament.salary'))
                                ->numeric(),

                            RichEditor::make('comment')
                                ->label(__('filament.comment'))
                                ->columnSpanFull(),
                            SpatieMediaLibraryFileUpload::make('position_file_attachments_attachments')
                                ->label(__('filament.position_file_attachments'))
                                ->collection('position')
                                ->removeUploadedFileButtonPosition('right')
                                ->multiple()
                                ->openable()
                                ->downloadable()
                                ->columnSpanFull()
                                ->extraAttributes(['class' => 'attachments-upload']),
                        ])
                        ->columns(2),
                    Tab::make(__('filament.vacation_policies'))
                        ->schema([
                            TextEntry::make('vacationPolicy.name'),
                            RepeatableEntry::make('vacationPolicy.settings')
                                ->schema([
                                    TextEntry::make('key')
                                        ->hiddenLabel(true)
                                        ->size(TextSize::Large)
                                        ->formatStateUsing(fn (string $state): string => __("filament.vacation_policy_settings.{$state}"))
                                        ->color('info')
                                        ->badge(),
                                    TextEntry::make('value')
                                        ->formatStateUsing(function ($state) {
                                            if (is_bool($state)) {
                                                return $state ? __('filament.vacation_policy_settings.yes') : __('filament.vacation_policy_settings.no');
                                            } else {
                                                return $state;
                                            }
                                        })
                                        ->hiddenLabel(true)
                                        ->size(TextSize::Large)
                                        ->color('info')
                                        ->badge(),
                                ])
                                ->columns(2),
                        ])
                        ->visible(fn (string $operation): bool => $operation === 'edit'),
                ])
                ->columnSpanFull(),
        ]);
    }

    private static function applyNonStaffTypeForPositionType(Set $set, mixed $positionTypeState): void
    {
        $type = $positionTypeState instanceof PositionType
            ? $positionTypeState
            : PositionType::tryFrom((string) $positionTypeState);

        if ($type?->isNonStaffPositionType()) {
            $set('staff_type', '2');
        }
    }

    private static function positionTypeShowsClinical(mixed $positionType): bool
    {
        $type = $positionType instanceof PositionType
            ? $positionType
            : PositionType::tryFrom($positionType);

        return $type?->showsClinicalFields() ?? false;
    }

    private static function positionTypeShowsAutomativeRenewal(mixed $positionType): bool
    {
        $type = $positionType instanceof PositionType
            ? $positionType
            : PositionType::tryFrom($positionType);

        return $type?->showsAutomativeRenewal() ?? false;
    }
}
