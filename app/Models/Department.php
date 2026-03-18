<?php

namespace App\Models;

use App\Enums\DepartmentStatus;
use App\Enums\EnumsDepartmentColor;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
            get: fn () => $this->name.' | Level '.(Department::find($this->parent_id)?->ancestors()->count() + 2) ?? 'Level 1 (Root)',
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
}
