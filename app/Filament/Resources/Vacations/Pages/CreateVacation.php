<?php

namespace App\Filament\Resources\Vacations\Pages;

use App\Filament\Resources\Vacations\VacationResource;
use App\Models\Position;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateVacation extends CreateRecord
{
    protected static string $resource = VacationResource::class;

    protected function beforeCreate(): void
    {
        $data = $this->form->getState();

        $position = Position::find($data['position_id']);
        $available = $position->available_vacation_days;
        $requested = (int) $data['working_days_count'];

        if ($available < $requested || $available == 0) {
            Notification::make()
                ->title(__('filament.vacation_insufficient_balance'))
                ->body(__('filament.vacation_insufficient_balance_body', [
                    'available' => $available,
                    'requested' => $requested,
                ]))
                ->seconds(5)
                ->duration(10000)
                ->color('warning')
                ->warning()
                ->send();

            $this->halt();
        }
    }
}
