<?php

namespace App\Filament\Resources\Vacations\Pages;

use App\Enums\VacationType;
use App\Filament\Resources\Vacations\VacationResource;
use App\Models\Position;
use App\Models\Vacation;
use Carbon\Carbon;
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

        if ($data['type']->value === VacationType::DAY_OFF->value) {
            $color = 'danger';
            if (Vacation::hasAdjacentHoliday($data['start_date'])) {
                $title = __('filament.day_off_adjacent_holiday');
                $body = __('filament.day_off_adjacent_holiday_body');
                Notification::make()
                    ->title($title)
                    ->body($body)
                    ->color($color)
                    ->send();
                $this->halt();
            }

            $days_off = Vacation::validateDayOff($data['employee_id'], $data['position_id'], Carbon::parse($data['start_date']));
            if ($days_off >= 5) {
                $title = __('filament.vacation_day_off_limit_exceeded');
                $body = __('filament.vacation_day_off_limit_exceeded_body', [
                    'days_off' => $days_off,
                ]);
                Notification::make()
                    ->title($title)
                    ->body($body)
                    ->color($color)
                    ->send();
                $this->halt();

            }

        }

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
