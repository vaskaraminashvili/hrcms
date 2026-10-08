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

class ScientificForum extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $table = 'scientific_forums';

    protected $fillable = [
        'employee_id',
        'sort',
        'title',
        'international_forum_publication',
        'held_at',
        'start_date',
        'end_date',
        'participation_form',
        'participation_role',
        'scope',
    ];

    public array $translatable = ['title', 'participation_form'];

    protected $attributes = [
        'international_forum_publication' => false,
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'participation_form' => 'array',
            'held_at' => 'date',
            'international_forum_publication' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(PersonalFile::SCIENTIFIC_FORUMS->mediaCollectionName());
    }
}
