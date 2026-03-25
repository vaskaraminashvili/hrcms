<?php

namespace App\Models;

use App\Enums\DepartmentStatus;
use App\Enums\EnumsDepartmentColor;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Openplain\FilamentTreeView\Concerns\HasTreeStructure;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
    use HasFactory, HasTreeStructure, LogsActivity;

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Department $department): void {
            if (blank($department->slug)) {
                $department->slug = Str::slug($department->name);
            }
        });
    }

    protected function level(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->parent_id === null) {
                    return $this->name.' | Level 1 (Root)';
                }

                return $this->name.' | Level '.($this->ancestors()->count() + 1);
            },
        );
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'author_id',
        'status',
        'parent_id',
        'order',
    ];

    protected $casts = [
        'color' => EnumsDepartmentColor::class,
        'status' => DepartmentStatus::class,
    ];

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }
}
