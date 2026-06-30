<?php

namespace App\Filament\Resources\VacationTransfers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VacationTransferForm
{
    public static function configure(Schema $schema, bool $showPosition = true): Schema
    {
        return $schema
            ->components([
                Select::make('position_id')
                    ->relationship('position', 'id')
                    ->required($showPosition)
                    ->visible($showPosition),
                TextInput::make('from_year')
                    ->label(__('filament.from_year'))
                    ->required()
                    ->numeric()
                    ->default(function () {

                        return now()->subYear()->year;
                    })
                    ->minValue(2000)
                    ->maxValue(2100),
                TextInput::make('to_year')
                    ->label(__('filament.to_year'))
                    ->required()
                    ->numeric()
                    ->default(now()->year)
                    ->disabled()
                    ->dehydrated()
                    ->minValue(2000)
                    ->maxValue(2100),
                TextInput::make('days_count')
                    ->label(__('filament.days_count'))
                    ->required()
                    ->numeric()
                    ->minValue(1),
            ]);
    }
}
