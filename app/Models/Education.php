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

class Education extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $table = 'educations';

    protected $fillable = [
        'employee_id',
        'institution',
        'program',
        'specialty',
        'started_at',
        'ended_at',
    ];

    public array $translatable = ['institution', 'program', 'specialty'];

    protected function casts(): array
    {
        return [
            'institution' => 'array',
            'program' => 'array',
            'specialty' => 'array',
            'started_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(PersonalFile::EDUCATION->mediaCollectionName());
    }
}
