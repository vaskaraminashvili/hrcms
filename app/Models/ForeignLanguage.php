<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ForeignLanguage extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'language',
        'level',
    ];

    public array $translatable = ['language', 'level'];

    protected function casts(): array
    {
        return [
            'language' => 'array',
            'level'    => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
