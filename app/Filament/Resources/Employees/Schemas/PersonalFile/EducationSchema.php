<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use Filament\Forms\Components\DatePicker;

class EducationSchema
{
    use HasTranslatableFields;

    public static function schema(): array
    {
        return [
            static::translatableField('institution', __('filament.personal_file.education.institution')),
            static::translatableField('program', __('filament.personal_file.education.program')),
            static::translatableField('specialty', __('filament.personal_file.education.specialty')),
            DatePicker::make('started_at')->label(__('filament.personal_file.dates.started_at')),
            DatePicker::make('ended_at')->label(__('filament.personal_file.dates.ended_at')),
        ];
    }
}
