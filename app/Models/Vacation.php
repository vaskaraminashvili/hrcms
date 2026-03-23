<?php

namespace App\Models;

use App\Enums\VacationStatus;
use App\Enums\VacationType;
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
        'type',
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
            'type' => VacationType::class,
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
}
