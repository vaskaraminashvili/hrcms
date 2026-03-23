<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;

trait HasTranslatableFields
{
    protected static function translatableTextInput(string $name, ?string $label = null, array $required = [true, true]): array
    {
        return [
            TextInput::make("{$name}.ka")
                ->label($label ? "{$label} (ქართული)" : 'ქართული')
                ->columnSpan(1)
                ->required($required[0]),
            TextInput::make("{$name}.en")
                ->label($label ? "{$label} (English)" : 'English')
                ->columnSpan(1)
                ->required($required[1]),
        ];
    }

    protected static function translatableField(string $name, ?string $label = null, array $required = [false, false]): Grid
    {
        return Grid::make(2)
            ->schema(static::translatableTextInput($name, $label, $required));
    }
}
