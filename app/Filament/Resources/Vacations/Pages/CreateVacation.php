<?php

namespace App\Filament\Resources\Vacations\Pages;

use App\Enums\VacationType;
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

        $type = VacationType::from($data['type']);
        $calendarYear = match ($type) {
            VacationType::CurrentYear => now()->year,
            VacationType::PreviousYear => now()->subYear()->year,
        };

        $position = Position::query()->findOrFail($data['position_id']);

        if ($position->vacation_days_per_year === null) {
            return;
        }

        $allocation = (int) $position->vacation_days_per_year;
        $used = Vacation::sumUsedWorkingDaysForEmployeeTypeAndYear(
            (int) $data['employee_id'],
            $type,
            $calendarYear,
        );
        $requested = (int) $data['working_days_count'];

        if ($used + $requested <= $allocation) {
            return;
        }

        $remaining = max(0, $allocation - $used);
        dd($remaining, $allocation);
        throw ValidationException::withMessages([
            'working_days_count' => __('filament.vacation_insufficient_balance', [
                'remaining' => $remaining,
                'allocation' => $allocation,
            ]),
        ]);
    }
}
