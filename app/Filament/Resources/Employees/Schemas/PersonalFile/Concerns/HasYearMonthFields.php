<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns;

use Carbon\Carbon;
use Filament\Forms\Components\TextInput;

trait HasYearMonthFields
{
    protected static function yearMonthField(string $name, ?string $label = null): TextInput
    {
        return TextInput::make($name)
            ->type('month')
            ->formatStateUsing(fn ($state) => filled($state)
                ? Carbon::parse($state)->format('Y-m')
                : null)
            ->dehydrateStateUsing(fn ($state) => filled($state)
                ? Carbon::parse($state)->startOfMonth()->toDateString()
                : null)
            ->label($label);
    }
}
