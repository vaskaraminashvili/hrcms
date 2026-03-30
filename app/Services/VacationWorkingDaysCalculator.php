<?php

namespace App\Services;

use App\Enums\VacationStatus;
use App\Models\Position;
use App\Models\PublicHoliday;
use App\Models\Vacation;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class VacationWorkingDaysCalculator
{
    /**
     * Count working days in [start, end] per position policy (weekends) minus public holidays (one row per calendar day).
     */
    public function countWorkingDaysInRange(
        CarbonInterface $start,
        CarbonInterface $end,
        Position $position,
    ): int {
        $position->loadMissing('vacationPolicy');

        [$saturdayAllowed, $sundayAllowed] = $this->weekendPolicyFromPosition($position);
        $publicHolidayDates = $this->publicHolidayDateStringsBetween($start, $end);

        $count = 0;
        foreach (CarbonPeriod::create($start, $end) as $date) {
            if (! $this->isCandidateWorkingDay($date, $saturdayAllowed, $sundayAllowed)) {
                continue;
            }
            if ($publicHolidayDates->contains($date->toDateString())) {
                continue;
            }
            $count++;
        }

        return $count;
    }

    /**
     * Public holidays that fall on days that would otherwise count as working days (after weekend rules).
     */
    public function countPublicHolidaysExcludedInRange(
        CarbonInterface $start,
        CarbonInterface $end,
        Position $position,
    ): int {
        $position->loadMissing('vacationPolicy');

        [$saturdayAllowed, $sundayAllowed] = $this->weekendPolicyFromPosition($position);
        $publicHolidayDates = $this->publicHolidayDateStringsBetween($start, $end);

        $count = 0;
        foreach (CarbonPeriod::create($start, $end) as $date) {
            if (! $this->isCandidateWorkingDay($date, $saturdayAllowed, $sundayAllowed)) {
                continue;
            }
            if ($publicHolidayDates->contains($date->toDateString())) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return array{0: bool, 1: bool} [saturday_allowed, sunday_allowed]
     */
    private function weekendPolicyFromPosition(Position $position): array
    {
        $settings = collect($position->vacationPolicy?->settings ?? []);

        return [
            (bool) ($settings->firstWhere('key', 'saturday_allowed')['value'] ?? false),
            (bool) ($settings->firstWhere('key', 'sunday_allowed')['value'] ?? false),
        ];
    }

    private function isCandidateWorkingDay(
        CarbonInterface $date,
        bool $saturdayAllowed,
        bool $sundayAllowed,
    ): bool {
        if ($date->isSaturday() && ! $saturdayAllowed) {
            return false;
        }
        if ($date->isSunday() && ! $sundayAllowed) {
            return false;
        }

        return true;
    }

    /**
     * Recalculate {@see Vacation::$working_days_count} for pending/approved vacations overlapping the date range.
     */
    public function recalculateVacationsOverlappingDateRange(
        CarbonInterface $rangeStart,
        CarbonInterface $rangeEnd,
    ): void {
        $rangeStart = $rangeStart->copy()->startOfDay();
        $rangeEnd = $rangeEnd->copy()->startOfDay();

        Vacation::query()
            ->whereIn('status', [
                VacationStatus::Pending,
                VacationStatus::Approved,
            ])
            ->whereDate('start_date', '<=', $rangeEnd)
            ->whereDate('end_date', '>=', $rangeStart)
            ->with(['position.vacationPolicy'])
            ->chunkById(100, function (Collection $vacations): void {
                foreach ($vacations as $vacation) {
                    /** @var Vacation $vacation */
                    $position = $vacation->position;
                    if ($position === null) {
                        continue;
                    }

                    $count = $this->countWorkingDaysInRange(
                        $vacation->start_date,
                        $vacation->end_date,
                        $position,
                    );

                    if ($vacation->working_days_count !== $count) {
                        $vacation->forceFill(['working_days_count' => $count])->saveQuietly();
                    }
                }
            });
    }

    /**
     * @return Collection<int, string> Date strings Y-m-d
     */
    private function publicHolidayDateStringsBetween(
        CarbonInterface $start,
        CarbonInterface $end,
    ): Collection {
        return PublicHoliday::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->map(fn (PublicHoliday $holiday) => $holiday->date->toDateString());
    }
}
