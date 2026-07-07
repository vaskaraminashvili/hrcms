<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Enums\LanguageProficiency;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class ForeignLanguagesSchema
{
    public static bool $fileUploadEnabled = true;

    public static function schema(): array
    {
        return [
            Select::make('level')
                ->label(__('filament.personal_file.foreign_languages.level'))
                ->options(collect(LanguageProficiency::cases())->mapWithKeys(
                    fn (LanguageProficiency $case) => [$case->value => $case->getDisplayLabel()]
                )),
            TextInput::make('language')
                ->label(__('filament.personal_file.foreign_languages.language')),
        ];
    }

    public static function fileUploadEnabled(): bool
    {
        return self::$fileUploadEnabled;
    }
}
