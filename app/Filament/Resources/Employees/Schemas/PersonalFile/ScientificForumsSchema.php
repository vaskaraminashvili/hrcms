<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use Filament\Forms\Components\DatePicker;

class ScientificForumsSchema
{
    use HasTranslatableFields;

    public static function schema(): array
    {
        return [
            static::translatableField('title', __('filament.personal_file.scientific_forums.title')),
            static::translatableField('participation_form', __('filament.personal_file.scientific_forums.participation_form')),
            DatePicker::make('held_at')->label(__('filament.personal_file.dates.held_at')),
        ];
    }
}
