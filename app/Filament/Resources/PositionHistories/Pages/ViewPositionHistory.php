<?php

namespace App\Filament\Resources\PositionHistories\Pages;

use App\Filament\Resources\PositionHistories\PositionHistoryResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPositionHistory extends ViewRecord
{
    protected static string $resource = PositionHistoryResource::class;

    public function getTitle(): string
    {
        return __('filament.position_history_title');
    }
}
