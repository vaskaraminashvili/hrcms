<?php

namespace App\Filament\Resources\PositionTypes\Pages;

use App\Filament\Resources\PositionTypes\PositionTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPositionType extends EditRecord
{
    protected static string $resource = PositionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
