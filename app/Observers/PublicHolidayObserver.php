<?php

namespace App\Observers;

use App\Models\PublicHoliday;
use App\Services\VacationWorkingDaysCalculator;
use Carbon\Carbon;

class PublicHolidayObserver
{
    public function __construct(
        private VacationWorkingDaysCalculator $vacationWorkingDaysCalculator,
    ) {}

    public function updated(PublicHoliday $publicHoliday): void
    {
        if (! $publicHoliday->wasChanged('date')) {
            return;
        }

        $original = $publicHoliday->getOriginal('date');
        $current = $publicHoliday->date;

        $min = $original instanceof Carbon ? $original->copy() : Carbon::parse($original);
        $max = $current instanceof Carbon ? $current->copy() : Carbon::parse($current);

        if ($min->gt($max)) {
            [$min, $max] = [$max, $min];
        }

        $this->vacationWorkingDaysCalculator->recalculateVacationsOverlappingDateRange($min, $max);
    }

    public function deleted(PublicHoliday $publicHoliday): void
    {
        $this->vacationWorkingDaysCalculator->recalculateVacationsOverlappingDateRange(
            $publicHoliday->date,
            $publicHoliday->date,
        );
    }
}
