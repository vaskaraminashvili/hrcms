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

class ScholarshipAward extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes;

    protected $table = 'scholarships_awards';

    protected $fillable = [
        'employee_id',
        'title',
        'issuer',
        'issued_at',
    ];

    public array $translatable = ['title', 'issuer'];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'issuer' => 'array',
            'issued_at' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(PersonalFile::SCHOLARSHIPS_AWARDS->mediaCollectionName());
    }
}
