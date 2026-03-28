<?php

namespace App\Filament\Resources\PositionHistories\Pages;

use App\Filament\Resources\PositionHistories\PositionHistoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPositionHistory extends ViewRecord
{
    protected static string $resource = PositionHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return __('filament.admin.view_position_history.title');
    }
}
