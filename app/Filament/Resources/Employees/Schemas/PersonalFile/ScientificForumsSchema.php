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
            static::translatableField('title', 'სახელწოდება'),
            static::translatableField('participation_form', 'მონაწილეობის ფორმა'),
            DatePicker::make('held_at')->label('ჩატარების თარიღი'),
        ];
    }
}
