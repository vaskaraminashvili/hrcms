<?php

namespace App\Filament\Resources\Places\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PlaceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required(),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
