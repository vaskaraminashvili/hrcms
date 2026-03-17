<?php

namespace App\Models;

use App\Enums\PositionType as PositionTypeEnum;
use Database\Factories\PositionTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PositionType extends Model
{
    /** @use HasFactory<PositionTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(Position::class, 'position_position_type')
            ->withTimestamps();
    }

    public function toEnum(): PositionTypeEnum
    {
        return PositionTypeEnum::from($this->name);
    }

    public static function fromEnum(PositionTypeEnum $enum): self
    {
        return self::firstOrCreate(['name' => $enum->name]);
    }
}
