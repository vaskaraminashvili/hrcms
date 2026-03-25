<?php

namespace App\Models;

use App\Enums\VacationStatus;
use Database\Factories\VacationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Vacation extends Model
{
    /** @use HasFactory<VacationFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'position_id',
        'start_date',
        'end_date',
        'working_days_count',
        'status',
        'reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date:d.m.Y',
            'end_date' => 'date:d.m.Y',
            'working_days_count' => 'integer',
            'status' => VacationStatus::class,
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
}
