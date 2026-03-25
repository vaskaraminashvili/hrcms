<?php

namespace App\Filament\Resources\VacationTransfers\Pages;

use App\Filament\Resources\VacationTransfers\VacationTransferResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVacationTransfers extends ListRecords
{
    protected static string $resource = VacationTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
