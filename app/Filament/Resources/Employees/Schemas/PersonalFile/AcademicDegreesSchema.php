<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Enums\AcademicDegree;
use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use Filament\Forms\Components\Select;

class AcademicDegreesSchema
{
    use HasTranslatableFields;

    public static function schema(): array
    {
        return [
            Select::make('degree')
                ->label(__('filament.personal_file.academic_degrees.degree'))
                ->options(collect(AcademicDegree::cases())->mapWithKeys(
                    fn (AcademicDegree $case) => [$case->value => $case->getLabel()]
                )),
            static::translatableField('other', __('filament.personal_file.academic_degrees.other')),
        ];
    }
}
