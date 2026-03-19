<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class AcademicPosition extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'academic_positions';

    protected $fillable = [
        'employee_id',
        'title',
    ];

    public array $translatable = ['title'];

    protected function casts(): array
    {
        return [
            'title' => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
