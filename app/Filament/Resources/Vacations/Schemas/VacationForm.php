<?php

namespace App\Filament\Resources\Vacations\Schemas;

use App\Enums\VacationStatus;
use App\Models\Position;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                    ->searchable(
                        ['name', 'surname', 'personal_number']
                    )
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
                    ->columnSpanFull()
                    ->label(__('filament.position'))
                    ->required($showEmployeeAndPosition)
                    ->visible($showEmployeeAndPosition),
                DatePicker::make('start_date')
                    ->required()
                    ->label(__('filament.start_date')),
                DatePicker::make('end_date')
                    ->required()
                    ->label(__('filament.end_date')),
                TextInput::make('working_days_count')
                    ->required()
                    ->numeric()
                    ->minValue(1)
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
            ]);
    }
}
