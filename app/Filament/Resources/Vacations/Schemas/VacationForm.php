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
                    ->required()
                    ->live()
                    ->label(__('filament.start_date'))
                    ->afterStateUpdated(function (Get $get, Set $set, mixed $livewire) {
                        self::recalculateDays($get, $set, self::resolvePosition($get, $livewire));
                    }),

                DatePicker::make('end_date')
                    ->required()
                    ->live()
                    ->label(__('filament.end_date'))
                    ->afterStateUpdated(function (Get $get, Set $set, mixed $livewire) {
                        self::recalculateDays($get, $set, self::resolvePosition($get, $livewire));
                    }),

                TextInput::make('working_days_count')
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->helperText(function (Get $get, Set $set, mixed $livewire) use ($showEmployeeAndPosition) {
                        $position = $showEmployeeAndPosition
                            ? Position::with('detail.vacationPolicy')->find($get('position_id'))
                            : self::resolvePosition($get, $livewire);
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
            ->columns($cols);
    }

    private static function resolvePosition(Get $get, mixed $livewire): ?Position
    {
        if ($livewire instanceof RelationManager) {
            $owner = $livewire->getOwnerRecord();

            return $owner instanceof Position ? $owner : null;
        }

        return Position::with('detail.vacationPolicy')->find($get('position_id'));
    }

    private static function recalculateDays(Get $get, Set $set, ?Position $record): void
    {
        $startDate = $get('start_date');
        $endDate = $get('end_date');
        $position = $record ? $record->load('detail.vacationPolicy') : Position::with('detail.vacationPolicy')->find($get('position_id'));
        if (! $startDate || ! $endDate || ! $position) {
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
