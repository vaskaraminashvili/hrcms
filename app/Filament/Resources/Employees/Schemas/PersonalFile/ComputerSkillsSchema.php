<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;

class ComputerSkillsSchema
{
    use HasTranslatableFields;

    public static function schema(): array
    {
        return [
            static::translatableField('title', __('filament.personal_file.computer_skills.title')),
            static::translatableField('level', __('filament.personal_file.computer_skills.level')),
        ];
    }
}
