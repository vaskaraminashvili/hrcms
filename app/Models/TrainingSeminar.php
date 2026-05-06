<?php

namespace App\Models;

use App\Enums\PersonalFile;
use App\Models\Concerns\RegistersConstrainedMediaCollections;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class TrainingSeminar extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, RegistersConstrainedMediaCollections, SoftDeletes;

    protected $table = 'trainings_seminars';

    protected $fillable = [
        'employee_id',
        'institution',
        'topic',
        'started_at',
        'ended_at',
    ];

    public array $translatable = ['institution', 'topic'];

    protected function casts(): array
    {
        return [
            'institution' => 'array',
            'topic' => 'array',
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
        $this->registerConstrainedMediaCollection(PersonalFile::TRAININGS_SEMINARS->mediaCollectionName());
    }
}
