<?php

namespace App\Models;

use App\Enums\PositionStatus;
use App\Enums\PositionType;
use Database\Factories\PositionDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PositionDetail extends Model
{
    /** @use HasFactory<PositionDetailFactory> */
    use HasFactory, LogsActivity;

    protected $fillable = [
        'position_type',
        'date_start',
        'date_end',
        'status',
        'act_number',
        'act_date',
        'staff_type',
        'clinical',
        'clinical_text',
        'automative_renewal',
        'salary',
        'vacation_policy_id',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'salary' => 'integer',
            'date_start' => 'date',
            'date_end' => 'date',
            'act_date' => 'date',
            'clinical' => 'boolean',
            'automative_renewal' => 'boolean',
            'status' => PositionStatus::class,
            'position_type' => PositionType::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->dontSubmitEmptyLogs();
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function vacationPolicy(): BelongsTo
    {
        return $this->belongsTo(VacationPolicy::class);
    }
}
