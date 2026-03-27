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

class ForeignLanguage extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $table = 'foreign_languages';

    protected $fillable = [
        'employee_id',
        'language',
        'level',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(PersonalFile::FOREIGN_LANGUAGES->mediaCollectionName());
    }
}
