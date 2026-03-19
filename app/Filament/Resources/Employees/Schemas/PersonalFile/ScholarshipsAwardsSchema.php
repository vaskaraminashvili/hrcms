<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use Filament\Forms\Components\DatePicker;

class ScholarshipsAwardsSchema
{
    use HasTranslatableFields;

    public static function schema(): array
    {
        return [
            static::translatableField('title', 'სახელწოდება'),
            static::translatableField('issuer', 'გამცემელი'),
            DatePicker::make('issued_at')->label('გაცემის თარიღი'),
        ];
    }
}
