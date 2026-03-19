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
                    ->label(__('filament/admin/place_resource.name'))
                    ->required(),
                Toggle::make('is_active')
                    ->label(__('filament/admin/place_resource.is_active'))
                    ->default(true),
            ]);
    }
}
