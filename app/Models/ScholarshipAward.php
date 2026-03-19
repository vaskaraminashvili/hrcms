<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ScholarshipAward extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'scholarships_awards';

    protected $fillable = [
        'employee_id',
        'title',
        'issuer',
        'issued_at',
    ];

    public array $translatable = ['title', 'issuer'];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'issuer' => 'array',
            'issued_at' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
