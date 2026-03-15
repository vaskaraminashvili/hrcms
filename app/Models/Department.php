<?php

namespace App\Models;

use App\Enums\EnumsDepartmentColor;
use App\Enums\EnumsDepartmentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Openplain\FilamentTreeView\Concerns\HasTreeStructure;

class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
    use HasFactory,HasTreeStructure;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'type',
        'author_id',
        'is_active',
        'parent_id',
        'order',
    ];

    protected $casts = [
        'type' => EnumsDepartmentType::class,
        'color' => EnumsDepartmentColor::class,
    ];
}
