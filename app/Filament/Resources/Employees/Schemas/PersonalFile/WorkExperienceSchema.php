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
            static::translatableField('institution', 'დაწესებულება'),
            static::translatableField('position', 'თანამდებობა'),
            DatePicker::make('started_at')->label('დაწყების თარიღი'),
            DatePicker::make('ended_at')->label('დასრულების თარიღი'),
        ];
    }
}
