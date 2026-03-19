<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ScientificForum extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'title',
        'held_at',
        'participation_form',
    ];

    public array $translatable = ['title', 'participation_form'];

    protected function casts(): array
    {
        return [
            'title'              => 'array',
            'participation_form' => 'array',
            'held_at'            => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
