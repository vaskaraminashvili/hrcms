<?php

namespace App\Models;

use App\Enums\PositionStatus;
use App\Enums\PositionType;
use Database\Factories\PositionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Position extends Model
{
    /** @use HasFactory<PositionFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'department_id',
        'place_id',
    ];

    protected $with = [
        'detail',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['place_id', 'employee_id', 'department_id'])
            ->dontSubmitEmptyLogs();
    }

    public function detail(): HasOne
    {
        return $this->hasOne(PositionDetail::class);
    }

    public function getPositionTypeAttribute(): ?PositionType
    {
        return $this->detail?->position_type;
    }

    public function getDateStartAttribute(): mixed
    {
        return $this->detail?->date_start;
    }

    public function getDateEndAttribute(): mixed
    {
        return $this->detail?->date_end;
    }

    public function getStatusAttribute(): ?PositionStatus
    {
        return $this->detail?->status;
    }

    public function getActNumberAttribute(): ?string
    {
        return $this->detail?->act_number;
    }

    public function getActDateAttribute(): mixed
    {
        return $this->detail?->act_date;
    }

    public function getStaffTypeAttribute(): mixed
    {
        return $this->detail?->staff_type;
    }

    public function getClinicalAttribute(): ?bool
    {
        return $this->detail?->clinical;
    }

    public function getClinicalTextAttribute(): ?string
    {
        return $this->detail?->clinical_text;
    }

    public function getAutomativeRenewalAttribute(): ?bool
    {
        return $this->detail?->automative_renewal;
    }

    public function getSalaryAttribute(): ?int
    {
        return $this->detail?->salary;
    }

    public function getVacationPolicyIdAttribute(): ?int
    {
        return $this->detail?->vacation_policy_id;
    }

    public function getCommentAttribute(): ?string
    {
        return $this->detail?->comment;
    }

    public function getVacationPolicyAttribute(): ?VacationPolicy
    {
        return $this->detail?->vacationPolicy;
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

    public function vacations(): HasMany
    {
        return $this->hasMany(Vacation::class);
    }

    public function vacationTransfers(): HasMany
    {
        return $this->hasMany(VacationTransfer::class);
    }

    /**
     * Days granted by policy for this position type.
     * Reads the "days" key from the JSON settings field.
     */
    public function getPolicyDaysAttribute(): int
    {
        $settings = $this->vacationPolicy?->settings ?? [];

        $days = collect($settings)
            ->firstWhere('key', 'days')['value'] ?? 0;

        return (int) $days;
    }

    /**
     * Days transferred from the previous year (current year only).
     */
    public function getTransferredDaysAttribute(): int
    {
        return (int) $this->vacationTransfers()
            ->where('to_year', now()->year)
            ->sum('days_count');
    }

    /**
     * Total days available this year = policy days + transferred days.
     */
    public function getTotalVacationDaysAttribute(): int
    {
        return $this->policy_days + $this->transferred_days;
    }

    /**
     * Days already used (sum of approved vacations).
     */
    public function getUsedVacationDaysAttribute(): int
    {
        return (int) $this->vacations()->sum('working_days_count');
    }

    /**
     * Remaining / available days = total - used.
     */
    public function getAvailableVacationDaysAttribute(): int
    {
        return max(0, $this->total_vacation_days - $this->used_vacation_days);
    }
}
