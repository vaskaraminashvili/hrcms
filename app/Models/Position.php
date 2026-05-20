<?php

namespace App\Models;

use App\Enums\PositionStatus;
use App\Enums\PositionType;
use App\Enums\VacationStatus;
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

    // get total of transferred days
    public function getTransferredDaysAttribute(): int
    {
        return $this->vacationTransfers()
            ->where('to_year', now()->year)
            ->sum('days_count');
    }

    public function getTotalDaysOffAttribute(): int
    {
        $settings = $this->vacationPolicy?->settings ?? [];

        $days = collect($settings)
            ->firstWhere('key', 'days_off')['value'] ?? 5;

        return (int) $days;
    }

    public function getLeftDaysOffAttribute(): int
    {
        return $this->total_days_off - $this->getUsedVacationDaysAttribute(calendarYear: true, type: VacationType::DAY_OFF->value);
    }

    public function getLeftCalendarYearDaysAttribute(): int
    {
        return $this->policy_days - $this->getUsedVacationDaysAttribute(calendarYear: true);
    }

    public function getVacationDaysLeftLastYearAttribute(): int // vacation_days_left_last_year
    {
        return $this->transferred_days - $this->getUsedVacationDaysAttribute(calendarYear: false);
    }

    public function getAvailableVacationDaysAttribute(): int
    {
        return $this->getLeftCalendarYearDaysAttribute() + $this->getVacationDaysLeftLastYearAttribute();
    }

    // get used vacation days
    public function getUsedVacationDaysAttribute(bool $calendarYear = true, string $type = VacationType::PAID_LEAVE->value): int
    {
        return $this->vacations()
            ->where('status', VacationStatus::Approved)
            ->where('type', $type)
            ->when($calendarYear,
                fn ($q) => $q->whereYear('start_date', now()->year),
                fn ($q) => $q->whereYear('start_date', '<', now()->year)
            )
            ->sum('working_days_count');
    }

    /**
     * Exclude dismissal rows whose end date is still in the future (not yet effective).
     */
    public function scopeExcludeScheduledDismissals(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNot('status', PositionStatus::Dismissal->value)
                ->where(function () use ($q) {
                    $q->whereNull('date_end')
                        ->OrWhereDate('date_end', '>', now());
                });
        });
    }

    /**
     * Positions whose end date is not in the past. A null end date means no fixed end.
     */
    public function scopeWhereDateEndNotExpired(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNull('date_end')
                ->orWhereDate('date_end', '>=', now());
        });
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function scopeActivePositions(Builder $query): Builder
    {
        return $query->whereNotIn('status', [PositionStatus::Dismissal->value, PositionStatus::Achieved->value])
            ->where(function (Builder $q): void {
                $q->whereNull('date_end')
                    ->orWhereDate('date_end', '>=', now());
            });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('position');
    }
}
