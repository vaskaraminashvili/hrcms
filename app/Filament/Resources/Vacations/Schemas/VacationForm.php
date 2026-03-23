<?php

namespace App\Filament\Resources\Vacations\Schemas;

use App\Enums\VacationStatus;
use App\Enums\VacationType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class VacationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->required(),
                Select::make('position_id')
                    ->relationship('position', 'id')
                    ->required(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date')
                    ->required(),
                TextInput::make('working_days_count')
                    ->required()
                    ->numeric(),
                Select::make('type')
                    ->options(VacationType::class)
                    ->required(),
                Select::make('status')
                    ->options(VacationStatus::class)
                    ->default('pending')
                    ->required(),
                Textarea::make('reason')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
