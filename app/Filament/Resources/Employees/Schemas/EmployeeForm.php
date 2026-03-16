<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('surname')
                    ->required(),
                TextInput::make('name_eng'),
                TextInput::make('surrname_eng'),
                TextInput::make('personal_number')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                DatePicker::make('birth_date')
                    ->required(),
                TextInput::make('gender'),
                TextInput::make('citizenship'),
                TextInput::make('education')
                    ->numeric(),
                TextInput::make('degree'),
                TextInput::make('address'),
                TextInput::make('pysical_address'),
            ]);
    }
}
