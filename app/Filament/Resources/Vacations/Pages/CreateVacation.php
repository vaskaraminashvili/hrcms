<?php

namespace App\Filament\Resources\Vacations\Pages;

use App\Filament\Resources\Vacations\VacationResource;
use App\Models\Position;
use App\Models\Vacation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateVacation extends CreateRecord
{
    protected static string $resource = VacationResource::class;

    protected function beforeCreate(): void
    {
        $data = $this->form->getState();

        $position = Position::query()->findOrFail($data['position_id']);

        $used = Vacation::sumUsedWorkingDaysForEmployeeTypeAndYear(
            (int) $data['employee_id'],
            now()->year,
        );
        $requested = (int) $data['working_days_count'];

        if ($used + $requested <= $allocation) {
            return;
        }

        $remaining = max(0, $allocation - $used);
        throw ValidationException::withMessages([
            'working_days_count' => __('filament.vacation_insufficient_balance', [
                'remaining' => $remaining,
                'allocation' => $allocation,
            ]),
        ]);
    }
}
