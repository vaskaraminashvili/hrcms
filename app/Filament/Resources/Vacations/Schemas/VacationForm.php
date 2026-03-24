<?php

namespace App\Filament\Resources\Vacations\Schemas;

use App\Enums\VacationStatus;
use App\Models\Position;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class VacationForm
{
    public static function configure(Schema $schema, bool $showEmployeeAndPosition = true): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' '.$record->surname)
                    ->searchable(['name', 'surname', 'personal_number'])
                    ->afterStateUpdated(function (Set $set, mixed $state) {
                        $set('position_id', null);
                    })
                    ->columnSpanFull()
                    ->label(__('filament.employee_id'))
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

                DatePicker::make('start_date')
                    ->minDate(now())
                    ->required()
                    ->live()
                    ->label(__('filament.start_date'))
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        self::recalculateDays($get, $set);
                    }),

                DatePicker::make('end_date')
                    ->required()
                    ->live()
                    ->label(__('filament.end_date'))
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        self::recalculateDays($get, $set);
                    }),

                TextInput::make('working_days_count')
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->helperText(function (Get $get) {
                        $positionId = $get('position_id');
                        $position = Position::with('vacationPolicy')->find($positionId);
                        if (! $position) {
                            return null;
                        }

                        $settings = collect($position->vacationPolicy?->settings ?? []);
                        $saturdayAllowed = (bool) ($settings->firstWhere('key', 'saturday_allowed')['value'] ?? false);
                        $sundayAllowed = (bool) ($settings->firstWhere('key', 'sunday_allowed')['value'] ?? false);

                        return __('filament.working_days_count_helper_text', [
                            'saturday_allowed' => $saturdayAllowed ? 'ითვლება' : 'არ ითვლება',
                            'sunday_allowed' => $sundayAllowed ? 'ითვლება' : 'არ ითვლება',
                        ]);
                    })
                    ->label(__('filament.working_days_count')),

                Select::make('status')
                    ->options(collect(VacationStatus::cases())->mapWithKeys(
                        fn (VacationStatus $case) => [$case->value => $case->label()]
                    ))
                    ->default(VacationStatus::Pending->value)
                    ->required()
                    ->label(__('filament.status')),

                Textarea::make('reason')
                    ->columnSpanFull()
                    ->label(__('filament.reason')),

                Textarea::make('notes')
                    ->columnSpanFull()
                    ->label(__('filament.notes')),
            ])
            ->columns(4);
    }

    private static function recalculateDays(Get $get, Set $set): void
    {
        $startDate = $get('start_date');
        $endDate = $get('end_date');
        $positionId = $get('position_id');

        if (! $startDate || ! $endDate || ! $positionId) {
            return;
        }

        $position = Position::with('vacationPolicy')->find($positionId);

        if (! $position) {
            return;
        }

        $settings = collect($position->vacationPolicy?->settings ?? []);
        $saturdayAllowed = (bool) ($settings->firstWhere('key', 'saturday_allowed')['value'] ?? false);
        $sundayAllowed = (bool) ($settings->firstWhere('key', 'sunday_allowed')['value'] ?? false);
        $count = 0;
        foreach (CarbonPeriod::create(Carbon::parse($startDate), Carbon::parse($endDate)) as $date) {
            if ($date->isSaturday() && ! $saturdayAllowed) {
                continue;
            }
            if ($date->isSunday() && ! $sundayAllowed) {
                continue;
            }
            $count++;
        }

        $set('working_days_count', $count);
    }
}
