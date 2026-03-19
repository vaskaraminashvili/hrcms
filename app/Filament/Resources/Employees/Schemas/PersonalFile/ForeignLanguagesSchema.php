<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;

class ForeignLanguagesSchema
{
    use HasTranslatableFields;

    public static function schema(): array
    {
        return [
            static::translatableField('language', 'ენა'),
            static::translatableField('level', 'ფლობის ხარისხი'),
        ];
    }
}
