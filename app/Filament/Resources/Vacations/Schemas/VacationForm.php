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
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
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
                    ->default(VacationType::VACATION->value)
                    ->columnSpan(2)
                    ->live()
                    ->label(__('filament.type'))
                    ->required(),
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' '.$record->surname)
                    ->searchable(['name', 'surname', 'personal_number'])
                    ->afterStateUpdated(function (Set $set, mixed $state) {
                        $set('position_id', null);
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
                            ->with(['department', 'place'])
                            ->get()
                            ->mapWithKeys(function (Position $position) {
                                return [
                                    $position->id => $position->place->name.'/'.$position->department->name,
                                ];
                            });
                    })
                    ->live()
                    ->columnSpanFull()
                    ->label(__('filament.position'))
                    ->required($showEmployeeAndPosition)
                    ->visible($showEmployeeAndPosition),
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
                        self::recalculateDays($get, $set, self::resolvePosition($get, $livewire));
                        if ($get('type')->value === VacationType::DAY_OFF->value) {
                            self::dayOffSettings($get, $set, self::resolvePosition($get, $livewire));
                        }
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

    private static function dayOffSettings(Get $get, Set $set, ?Position $record): void
    {

        $hasAdjacentHoliday = Vacation::hasAdjacentHoliday($get('start_date'));
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
}
