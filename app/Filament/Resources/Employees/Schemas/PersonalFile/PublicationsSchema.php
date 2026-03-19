<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;

class PublicationsSchema
{
    use HasTranslatableFields;

    public static function schema(): array
    {
        return [
            static::translatableField('title', 'სახელწოდება'),
            static::translatableField('place', 'გამოქვეყნების ადგილი'),
            static::translatableField('co_authors', 'თანაავტორები'),
            DatePicker::make('published_at')->label('გამოქვეყნების თარიღი'),
            TextInput::make('page_count')
                ->label('გვერდების რაოდენობა')
                ->numeric(),
        ];
    }
}
