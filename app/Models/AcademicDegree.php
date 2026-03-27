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

class AcademicDegree extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $table = 'academic_degrees';

    protected $fillable = [
        'employee_id',
        'degree',
        'other',
    ];

    protected $casts = [
        'other' => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(PersonalFile::ACADEMIC_DEGREES->mediaCollectionName());
    }
}
