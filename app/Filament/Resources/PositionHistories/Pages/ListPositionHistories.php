<?php

namespace App\Filament\Resources\PositionHistories\Pages;

use App\Filament\Resources\PositionHistories\PositionHistoryResource;
use Filament\Resources\Pages\ListRecords;

class ListPositionHistories extends ListRecords
{
    protected static string $resource = PositionHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
