<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacationTransfer extends Model
{
    protected $fillable = [
        'position_id',
        'from_year',
        'to_year',
        'days_count',
    ];

    protected function casts(): array
    {
        return [
            'from_year' => 'year',
            'to_year' => 'year',
            'days_count' => 'integer',
        ];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
