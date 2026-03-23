<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use Filament\Forms\Components\DatePicker;

class WorkExperienceSchema
{
    use HasTranslatableFields;

    public static function schema(): array
    {
        return [
            static::translatableField('institution', __('filament.personal_file.work_experience.institution')),
            static::translatableField('position', __('filament.personal_file.work_experience.position')),
            DatePicker::make('started_at')->label(__('filament.personal_file.dates.started_at')),
            DatePicker::make('ended_at')->label(__('filament.personal_file.dates.ended_at')),
        ];
    }
}
