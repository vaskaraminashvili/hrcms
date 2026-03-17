<?php

namespace App\Models;

use App\Enums\PositionStatus;
use App\Enums\PositionType;
use Database\Factories\PositionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    /** @use HasFactory<PositionFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'department_id',
        'place_id',
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
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'salary' => 'integer',
            'date_start' => 'date',
            'date_end' => 'date',
            'act_date' => 'date',
            'staff_type' => 'boolean',
            'clinical' => 'boolean',
            'automative_renewal' => 'boolean',
            'status' => PositionStatus::class,
            'position_type' => PositionType::class,
        ];
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
