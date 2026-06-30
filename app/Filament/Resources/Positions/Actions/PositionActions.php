<?php

namespace App\Filament\Resources\Positions\Actions;

use Filament\Actions\Action;

class PositionActions
{
    public static function getActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament.save'))
                ->color('gray')
                ->action(function (): void {
                    $this->skipPositionObserverOnNextSave = true;
                    try {
                        $this->save();
                    } finally {
                        $this->skipPositionObserverOnNextSave = false;
                    }
                })
                ->keyBindings(['mod+s']),
            Action::make('saveWithHistory')
                ->label(__('filament.save_history'))
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading(__('filament.position_edit.modal_save_history_heading'))
                ->modalDescription(__('filament.position_edit.modal_save_history_description'))
                ->modalSubmitActionLabel(__('filament.position_edit.modal_save_history_submit'))
                ->action(function (): void {
                    $this->skipPositionObserverOnNextSave = false;
                    try {
                        $this->save();
                    } finally {
                        $this->skipPositionObserverOnNextSave = false;
                    }
                }),
            $this->getCancelFormAction(),
        ];
    }
}
