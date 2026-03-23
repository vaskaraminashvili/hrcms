<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;

class AcademicDegreesSchema
{
    use HasTranslatableFields;

    public static function schema(): array
    {
        return [
            static::translatableField('degree', __('filament.personal_file.academic_degrees.degree')),
            static::translatableField('other', __('filament.personal_file.academic_degrees.other')),
        ];
    }
}
