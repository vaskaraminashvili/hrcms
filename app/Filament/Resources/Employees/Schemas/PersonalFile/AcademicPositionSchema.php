<?php

namespace App\Filament\Resources\Employees\Schemas\PersonalFile;

use App\Enums\AcademicPosition;
use App\Filament\Resources\Employees\Schemas\PersonalFile\Concerns\HasTranslatableFields;
use Filament\Forms\Components\Select;

class AcademicPositionSchema
{
    use HasTranslatableFields;

    public static function schema(): array
    {
        return [
            Select::make('title')
                ->label(__('filament.personal_file.academic_position.title'))
                ->options(collect(AcademicPosition::cases())->mapWithKeys(
                    fn (AcademicPosition $case) => [$case->value => $case->getLabel()]
                )),
        ];
    }
}
