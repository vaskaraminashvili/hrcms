<?php

namespace App\Models;

use App\Enums\PositionType;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VacationPolicy extends Model
{
    protected $fillable = [
        'position_type',
        'name',
        'description',
        'color',
        'icon',
        'status',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'position_type' => PositionType::class,
            'status' => StatusEnum::class,
            'settings' => 'array',
        ];
    }

    public function vacations(): HasMany
    {
        return $this->hasMany(Vacation::class);
    }

    public function getColorAttribute(): string
    {
        return $this->status->color();
    }

    public function getIconAttribute(): string
    {
        return $this->status->icon()->value; // or ->name depending on Filament version
    }
}
