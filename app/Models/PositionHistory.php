<?php

namespace App\Models;

use App\Enums\PositionHistoryAffectField;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PositionHistory extends Model
{
    protected $fillable = [
        'position_id',
        'changed_by',
        'event_type',
        'snapshot',
        'changed_fields',
        'affects_salary',
        'affects_status',
        'affects_position_type',
        'affects_staff_type',
        'affects_date_start',
        'affects_date_end',
        'affects_clinical',
        'affects_place',
        'affects_act_number',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'changed_fields' => 'array',
        'affects_salary' => 'boolean',
        'affects_status' => 'boolean',
        'affects_position_type' => 'boolean',
        'affects_staff_type' => 'boolean',
        'affects_date_start' => 'boolean',
        'affects_date_end' => 'boolean',
        'affects_clinical' => 'boolean',
        'affects_place' => 'boolean',
        'affects_act_number' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->whereHas('position', fn ($q) => $q->where('employee_id', $employeeId));
    }

    public function scopeForDepartment(Builder $query, int $departmentId): Builder
    {
        return $query->whereHas('position', fn ($q) => $q->where('department_id', $departmentId));
    }

    public function scopeWhereAffects(Builder $query, PositionHistoryAffectField $field): Builder
    {
        return $query->where($field->value, true);
    }

    public function scopeSalaryChanges(Builder $query): Builder
    {
        return $query->whereAffects(PositionHistoryAffectField::Salary);
    }

    public function scopeStatusChanges(Builder $query): Builder
    {
        return $query->whereAffects(PositionHistoryAffectField::Status);
    }

    public function scopePositionTypeChanges(Builder $query): Builder
    {
        return $query->whereAffects(PositionHistoryAffectField::PositionType);
    }

    public function scopeStaffTypeChanges(Builder $query): Builder
    {
        return $query->whereAffects(PositionHistoryAffectField::StaffType);
    }

    public function scopeClinicalChanges(Builder $query): Builder
    {
        return $query->whereAffects(PositionHistoryAffectField::Clinical);
    }

    public function scopeInDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function getSalaryFrom(): ?int
    {
        return $this->changed_fields['salary']['from'] ?? null;
    }

    public function getSalaryTo(): ?int
    {
        return $this->changed_fields['salary']['to'] ?? null;
    }
}
