<?php

namespace App\Models;

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
        'date_start',
        'date_end',
        'status',
        'act_number',
        'act_date',
        'automative_renewal',
        'salary',
        'comment',
        'department_id',
    ];

    protected $casts = [
        'salary' => 'integer',
        'date_start' => 'date',
        'date_end' => 'date',
        'act_date' => 'date',
        'automative_renewal' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
