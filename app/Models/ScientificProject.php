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

class ScientificProject extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $table = 'scientific_projects';

    protected $fillable = [
        'employee_id',
        'project_name',
        'institution',
        'position',
        'started_at',
        'ended_at',
    ];

    public array $translatable = ['project_name', 'institution', 'position'];

    protected function casts(): array
    {
        return [
            'project_name' => 'array',
            'institution' => 'array',
            'position' => 'array',
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
        $this->addMediaCollection(PersonalFile::SCIENTIFIC_PROJECTS->mediaCollectionName());
    }
}
