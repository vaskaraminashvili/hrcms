<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use Filament\Forms\Components\DatePicker;

class ScholarshipsAwardsSchema
{
    use HasTranslatableFields;

    public static function schema(): array
    {
        return [
            static::translatableField('title', __('filament.personal_file.scholarships_awards.title')),
            static::translatableField('issuer', __('filament.personal_file.scholarships_awards.issuer')),
            DatePicker::make('issued_at')->label(__('filament.personal_file.dates.issued_at')),
        ];
    }
}
