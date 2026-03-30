<?php

namespace App\Models;

use App\Enums\PublicHolidayKind;
use Database\Factories\PublicHolidayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicHoliday extends Model
{
    /** @use HasFactory<PublicHolidayFactory> */
    use HasFactory;

    protected $fillable = [
        'date',
        'kind',
        'series_id',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'kind' => PublicHolidayKind::class,
        ];
    }
}
