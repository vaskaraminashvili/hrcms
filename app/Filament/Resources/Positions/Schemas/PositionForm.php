<?php

namespace App\Filament\Resources\Positions\Schemas;

use App\Enums\DepartmentStatus;
use App\Enums\PositionStatus;
use App\Enums\PositionType;
use App\Models\Department;
use App\Models\Position;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PositionForm
{
    public static function configure(Schema $schema, bool $withEmployee = true): Schema
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
                ->disabled(fn (?Position $record): bool => $record !== null)
                ->dehydrated()
                ->required()
                ->columnSpanFull()
                ->visible($withEmployee),

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
                                        ->required(fn ($get): bool => self::positionTypeShowsClinical($get('position_type')))
                                        ->visible(fn ($get): bool => self::positionTypeShowsClinical($get('position_type'))),

                                    TextInput::make('clinical_text')
                                        ->label(__('filament.clinical_text'))
                                        ->visible(fn ($get): bool => self::positionTypeShowsClinical($get('position_type')))
                                        ->required(fn ($get): bool => self::positionTypeShowsClinical($get('position_type'))),
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
                        ])
                        ->columns(2),
                ])
                ->columnSpanFull(),
        ]);
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
