<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use Filament\Forms\Components\DatePicker;

class ScientificProjectsSchema
{
    use HasTranslatableFields;

    public static function schema(): array
    {
        return [
            static::translatableField('project_name', __('filament.personal_file.scientific_projects.project_name')),
            static::translatableField('institution', __('filament.personal_file.scientific_projects.institution')),
            static::translatableField('position', __('filament.personal_file.scientific_projects.position')),
            DatePicker::make('started_at')->label(__('filament.personal_file.dates.started_at')),
            DatePicker::make('ended_at')->label(__('filament.personal_file.dates.ended_at')),
        ];
    }
}
