<?php

namespace App\Filament\Resources\VacationTransfers\Pages;

use App\Filament\Resources\VacationTransfers\VacationTransferResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVacationTransfer extends EditRecord
{
    protected static string $resource = VacationTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
