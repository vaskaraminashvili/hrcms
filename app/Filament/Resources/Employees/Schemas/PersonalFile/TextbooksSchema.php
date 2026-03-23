<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;

class TextbooksSchema
{
    use HasTranslatableFields;

    public static function schema(): array
    {
        return [
            static::translatableField('title', __('filament.personal_file.textbooks.title')),
            static::translatableField('publisher', __('filament.personal_file.textbooks.publisher')),
            static::translatableField('co_authors', __('filament.personal_file.textbooks.co_authors')),
            DatePicker::make('published_at')->label(__('filament.personal_file.dates.published_at')),
            TextInput::make('page_count')
                ->label(__('filament.personal_file.page_count'))
                ->numeric(),
        ];
    }
}
