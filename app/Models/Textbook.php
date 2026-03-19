<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Textbook extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'textbooks';

    protected $fillable = [
        'employee_id',
        'title',
        'publisher',
        'published_at',
        'co_authors',
        'page_count',
    ];

    public array $translatable = ['title', 'publisher', 'co_authors'];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'publisher' => 'array',
            'co_authors' => 'array',
            'published_at' => 'date',
            'page_count' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
