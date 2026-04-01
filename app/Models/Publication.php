<?php

namespace App\Models;

use App\Enums\PersonalFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Publication extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $table = 'publications';

    protected $fillable = [
        'employee_id',
        'title',
        'place',
        'published_at',
        'co_authors',
        'page_count',
    ];

    public array $translatable = ['title', 'place', 'co_authors'];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'place' => 'array',
            'co_authors' => 'array',
            'published_at' => 'integer',
            'page_count' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(PersonalFile::PUBLICATIONS->mediaCollectionName());
    }
}
