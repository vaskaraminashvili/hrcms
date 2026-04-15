<?php

namespace App\Models;

use App\Enums\VacationStatus;
use App\Enums\VacationType;
use Carbon\Carbon;
use Database\Factories\VacationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Vacation extends Model implements HasMedia
{
    /** @use HasFactory<VacationFactory> */
    use HasFactory, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'position_id',
        'start_date',
        'end_date',
        'working_days_count',
        'status',
        'reason',
        'notes',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date:d.m.Y',
            'end_date' => 'date:d.m.Y',
            'working_days_count' => 'integer',
            'status' => VacationStatus::class,
            'type' => VacationType::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->dontSubmitEmptyLogs();
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function scopeDayOffs(Builder $q): Builder
    {
        return $q->where('type', VacationType::DAY_OFF);
    }

    public function scopeVacations(Builder $q): Builder
    {
        return $q->where('type', VacationType::PAID_LEAVE);
    }

    public static function hasAdjacentHoliday(Carbon $date): bool
    {
        $dayBefore = $date->copy()->subDay();
        $dayAfter = $date->copy()->addDay();

        $hasAdjacentHoliday = PublicHoliday::whereBetween('date', [$dayBefore, $dayAfter])->exists();

        return $hasAdjacentHoliday;
    }

    public static function validateDayOff(int $employeeId, int $positionId, Carbon $date, int $limitPerYear = 5): int
    {
        // 1. Check annual quota
        $used = static::dayOffs()
            ->where('employee_id', $employeeId)
            ->where('position_id', $positionId)
            ->whereYear('start_date', $date->year)
            ->count();

        if ($used >= $limitPerYear) {
            return $used;
        }

        // 2. Cannot be the day after a public holiday
        $dayBefore = $date->copy()->subDay();
        $isAfterHoliday = PublicHoliday::whereDate('date', $dayBefore)->exists();

        if ($isAfterHoliday) {
            return $used;
        }

        return 0;
    }

    /**
     * Sum working days already booked for an employee for a vacation type and calendar year
     * (year is taken from {@see Vacation::$start_date}).
     *
     * Pending and approved requests consume the balance; rejected and cancelled do not.
     */
    public static function sumUsedWorkingDaysForEmployeeTypeAndYear(
        int $employeeId,
        int $calendarYear,
    ): int {
        return (int) static::query()
            ->where('employee_id', $employeeId)
            ->whereYear('start_date', $calendarYear)
            ->whereIn('status', [
                VacationStatus::Pending,
                VacationStatus::Approved,
            ])
            ->sum('working_days_count');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('vacation');
    }
}
