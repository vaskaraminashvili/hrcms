<?php

namespace App\Filament\Resources\Vacations\Schemas;

use App\Enums\VacationStatus;
use App\Enums\VacationType;
use App\Models\Position;
use App\Models\Vacation;
use App\Services\VacationWorkingDaysCalculator;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class VacationForm
{
    public static function configure(Schema $schema, bool $showEmployeeAndPosition = true): Schema
    {
        $cols = 4;
        if (! $showEmployeeAndPosition) {
            $cols = 2;
        }

        return $schema
            ->components([
                Select::make('type')
                    ->options(VacationType::class)
                    ->default(VacationType::PAID_LEAVE->value)
                    ->columnSpan(2)
                    ->live()
                    ->label(__('filament.type'))
                    ->afterStateUpdated(function (Get $get, Set $set, mixed $livewire) {
                        if ($get('type')?->value === VacationType::DAY_OFF->value && filled($get('start_date'))) {
                            self::dayOffSettings(Carbon::parse($get('start_date')), $set);
                        }
                    })
                    ->required(),
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' '.$record->surname)
                    ->searchable(['name', 'surname', 'personal_number'])
                    ->afterStateUpdated(function (Set $set, mixed $state) {
                        $set('position_id', null);
                        self::fillVacationDaysForSelectedPosition($set, null);
                    })
                    ->columnSpanFull()
                    ->label(__('filament.employee_id'))
                    ->columnSpan(2)
                    ->live()
                    ->preload()
                    ->required($showEmployeeAndPosition)
                    ->visible($showEmployeeAndPosition),

                Select::make('position_id')
                    ->options(function ($get) {
                        return Position::query()
                            ->where('employee_id', $get('employee_id'))
                            ->activePositions()
                            ->with(['department', 'place'])
                            ->get()
                            ->mapWithKeys(function (Position $position) {
                                return [
                                    $position->id => $position->place->name.'/'.$position->department->name,
                                ];
                            });
                    })
                    ->live()
                    ->afterStateUpdated(function (Set $set, mixed $state): void {
                        self::fillVacationDaysForSelectedPosition($set, $state);
                    })
                    ->columnSpanFull()
                    ->label(__('filament.position'))
                    ->required($showEmployeeAndPosition)
                    ->visible($showEmployeeAndPosition),
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
                    ->visible(fn (Get $get): bool => $showEmployeeAndPosition && filled($get('employee_id')) && filled($get('position_id')))

                    ->columns(5)
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(collect(VacationStatus::cases())->mapWithKeys(
                        fn (VacationStatus $case) => [$case->value => $case->label()]
                    ))
                    ->default(VacationStatus::Pending->value)
                    ->required()
                    ->label(__('filament.status')),
                DatePicker::make('start_date')
                    ->required()
                    ->live()
                    ->label(__('filament.start_date'))
                    ->afterStateUpdated(function (Get $get, Set $set, mixed $livewire) {
                        self::dayOffSettings(Carbon::parse($get('start_date')), $set);
                        self::recalculateDays($get, $set, self::resolvePosition($get, $livewire));
                    }),

                DatePicker::make('end_date')
                    ->required()
                    ->live()
                    ->label(__('filament.end_date'))
                    ->afterStateUpdated(function (Get $get, Set $set, mixed $livewire) {
                        self::recalculateDays($get, $set, self::resolvePosition($get, $livewire));
                    })
                    ->dehydrated()
                    ->disabled(fn (Get $get) => $get('type')?->value === VacationType::DAY_OFF->value),

                TextInput::make('working_days_count')
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->live()
                    ->helperText(function (Get $get, mixed $livewire) use ($showEmployeeAndPosition) {
                        $position = $showEmployeeAndPosition
                            ? Position::with('vacationPolicy')->find($get('position_id'))
                            : self::resolvePosition($get, $livewire);
                        if (! $position) {
                            return null;
                        }

                        $settings = collect($position->vacationPolicy?->settings ?? []);
                        $saturdayAllowed = (bool) ($settings->firstWhere('key', 'saturday_allowed')['value'] ?? false);
                        $sundayAllowed = (bool) ($settings->firstWhere('key', 'sunday_allowed')['value'] ?? false);

                        $saturdayLabel = $saturdayAllowed
                            ? __('filament.vacation_policy_settings.yes')
                            : __('filament.vacation_policy_settings.no');
                        $sundayLabel = $sundayAllowed
                            ? __('filament.vacation_policy_settings.yes')
                            : __('filament.vacation_policy_settings.no');

                        $publicHolidaysLine = '';
                        $startDate = $get('start_date');
                        $endDate = $get('end_date');
                        if ($startDate && $endDate) {
                            $excluded = app(VacationWorkingDaysCalculator::class)->countPublicHolidaysExcludedInRange(
                                Carbon::parse($startDate),
                                Carbon::parse($endDate),
                                $position,
                            );
                            $publicHolidaysLine = $excluded === 0
                                ? __('filament.working_days_count_helper_public_holidays_none')
                                : __('filament.working_days_count_helper_public_holidays_some', ['count' => $excluded]);
                        }

                        return __('filament.working_days_count_helper_text', [
                            'saturday' => $saturdayLabel,
                            'sunday' => $sundayLabel,
                            'public_holidays_line' => $publicHolidaysLine,
                        ]);
                    })
                    ->label(__('filament.working_days_count')),

                Textarea::make('reason')
                    ->columnSpanFull()
                    ->label(__('filament.reason')),

                Textarea::make('notes')
                    ->columnSpanFull()
                    ->label(__('filament.notes')),
                SpatieMediaLibraryFileUpload::make('position_file_attachments_attachments')
                    ->label(__('filament.vacation_file_attachments'))
                    ->collection('position')
                    ->removeUploadedFileButtonPosition('right')
                    ->multiple()
                    ->openable()
                    ->downloadable()
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'attachments-upload']),
            ])
            ->columns($cols);
    }

    private static function resolvePosition(Get $get, mixed $livewire): ?Position
    {
        if ($livewire instanceof RelationManager) {
            $owner = $livewire->getOwnerRecord();

            return $owner instanceof Position ? $owner : null;
        }

        return Position::with('vacationPolicy')->find($get('position_id'));
    }

    private static function dayOffSettings(Carbon $startDate, Set $set): void
    {

        $hasAdjacentHoliday = Vacation::hasAdjacentHoliday($startDate);
        if ($hasAdjacentHoliday) {
            Notification::make()
                ->title(__('filament.day_off_adjacent_holiday'))
                ->body(__('filament.day_off_adjacent_holiday_body'))
                ->seconds(5)
                ->duration(10000)
                ->color('warning')
                ->warning()
                ->send();

        }

    }

    private static function recalculateDays(Get $get, Set $set, ?Position $record): void
    {
        $startDate = $get('start_date');
        $endDate = $get('end_date');
        $position = $record ? $record->load('vacationPolicy') : Position::with('vacationPolicy')->find($get('position_id'));
        if (! $startDate || ! $position) {
            return;
        }
        if ($get('type')->value === VacationType::DAY_OFF->value) {
            $count = 1;
            $set('end_date', $startDate);
        } else {
            $count = app(VacationWorkingDaysCalculator::class)->countWorkingDaysInRange(
                Carbon::parse($startDate),
                Carbon::parse($endDate),
                $position,
            );
        }
        $set('working_days_count', $count);
    }

    private static function fillVacationDaysForSelectedPosition(Set $set, mixed $positionId): void
    {
        $position = Position::query()
            ->with(['vacationPolicy', 'vacations', 'vacationTransfers'])
            ->find($positionId);

        if (! $position) {
            $defaults = ['used_days_off_days', 'transferred_days', 'total_vacation_days', 'used_vacation_days', 'available_vacation_days'];
            foreach ($defaults as $field) {
                $set($field, 0);
            }

            return;
        }

        $set('used_days_off_days', $position->used_days_off_days);
        $set('transferred_days', $position->transferred_days);
        $set('total_vacation_days', $position->total_vacation_days);
        $set('used_vacation_days', $position->used_vacation_days);
        $set('available_vacation_days', $position->available_vacation_days);
    }
}
