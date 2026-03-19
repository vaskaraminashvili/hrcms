<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ComputerSkill extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'computer_skills';

    protected $fillable = [
        'employee_id',
        'title',
        'level',
    ];

    public array $translatable = ['title', 'level'];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'level' => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
