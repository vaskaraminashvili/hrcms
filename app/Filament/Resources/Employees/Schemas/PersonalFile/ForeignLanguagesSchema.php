<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;

class ForeignLanguagesSchema
{
    use HasTranslatableFields;

    public static function schema(): array
    {
        return [
            static::translatableField('language', __('filament.personal_file.foreign_languages.language')),
            static::translatableField('level', __('filament.personal_file.foreign_languages.level')),
        ];
    }
}
