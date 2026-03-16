<?php

namespace App\Models;

use App\Enums\EnumsDepartmentColor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Openplain\FilamentTreeView\Concerns\HasTreeStructure;

class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
    use HasFactory, HasTreeStructure;

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Department $department): void {
            if (blank($department->slug)) {
                $department->slug = Str::slug($department->name);
            }
        });
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'author_id',
        'is_active',
        'parent_id',
        'order',
    ];

    protected $casts = [
        'color' => EnumsDepartmentColor::class,
    ];
}
