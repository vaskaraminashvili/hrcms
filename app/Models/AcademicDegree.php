<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class AcademicDegree extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'degree',
        'other',
    ];

    public array $translatable = ['degree', 'other'];

    protected function casts(): array
    {
        return [
            'degree' => 'array',
            'other'  => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
