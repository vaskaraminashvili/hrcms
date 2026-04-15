<?php

namespace App\Models;

use App\Enums\PositionStatus;
use App\Enums\PositionType;
use App\Enums\VacationType;
use Database\Factories\PositionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Position extends Model implements HasMedia
{
    /** @use HasFactory<PositionFactory> */
    use HasFactory, InteractsWithMedia, LogsActivity;

    protected $fillable = [
        'employee_id',
        'department_id',
        'place_id',
        'vacation_policy_id',
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
            'clinical' => 'boolean',
            'automative_renewal' => 'boolean',
            'status' => PositionStatus::class,
            'position_type' => PositionType::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'place_id',
                'employee_id',
                'department_id',
                'vacation_policy_id',
                'position_type',
                'date_start',
                'date_end',
                'status',
                'salary',
            ])
            ->dontSubmitEmptyLogs();
    }

    public function vacationPolicy(): BelongsTo
    {
        return $this->belongsTo(VacationPolicy::class);
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

    public function histories(): HasMany
    {
        return $this->hasMany(PositionHistory::class);
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

    public function getUsedDaysOffDaysAttribute(): int
    {
        return (int) $this->vacations()->where('type', VacationType::DAY_OFF)->count();
    }

    /**
     * Remaining / available days = total - used.
     */
    public function getAvailableVacationDaysAttribute(): int
    {
        return max(0, $this->total_vacation_days - $this->used_vacation_days);
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function scopeActivePositions(Builder $query): Builder
    {
        return $query->whereNotIn('status', [PositionStatus::Dismissal->value, PositionStatus::Achieved->value])
            ->where('date_end', '>=', now());
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('position');
    }
}
