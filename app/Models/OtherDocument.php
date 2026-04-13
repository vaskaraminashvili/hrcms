<?php

namespace App\Models;

use App\Enums\PersonalFile;
use Database\Factories\OtherDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class OtherDocument extends Model implements HasMedia
{
    /** @use HasFactory<OtherDocumentFactory> */
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $table = 'other_documents';

    protected $fillable = [
        'employee_id',
        'title',
    ];

    public array $translatable = ['title'];

    protected function casts(): array
    {
        return [
            'title' => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(PersonalFile::OTHER->mediaCollectionName());
    }
}
