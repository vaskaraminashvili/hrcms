<?php

namespace App\Models;

use App\Enums\PersonalFile;
use App\Enums\PublicationScope;
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
        'sort',
        'title',
        'place',
        'published_at',
        'co_authors',
        'page_count',
        'publication_details',
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
            'publication_details' => 'array',
        ];
    }

    /**
     * @return array{scope: string, indexed: bool, impact_factor: bool}
     */
    public static function defaultPublicationDetails(): array
    {
        return [
            'scope' => PublicationScope::Local->value,
            'indexed' => false,
            'impact_factor' => false,
        ];
    }

    /**
     * @return array{scope: string, indexed: bool, impact_factor: bool}
     */
    public static function normalizePublicationDetails(mixed $scope, bool $indexed, bool $impactFactor): array
    {
        $resolved = $scope instanceof PublicationScope
            ? $scope
            : (is_string($scope) ? PublicationScope::tryFrom($scope) : null);

        if ($resolved !== PublicationScope::International) {
            return self::defaultPublicationDetails();
        }

        return [
            'scope' => PublicationScope::International->value,
            'indexed' => $indexed,
            'impact_factor' => $indexed && $impactFactor,
        ];
    }

    /**
     * @param  list<int|string>  $publicationIds
     * @param  array{scope: string, indexed: bool, impact_factor: bool}  $details
     */
    public static function applyPublicationDetails(int $employeeId, array $publicationIds, array $details): int
    {
        $ids = array_values(array_filter(
            array_map(intval(...), $publicationIds),
            fn (int $id): bool => $id > 0,
        ));

        if ($ids === []) {
            return 0;
        }

        $publications = static::query()
            ->where('employee_id', $employeeId)
            ->whereIn('id', $ids)
            ->get();

        foreach ($publications as $publication) {
            $publication->update([
                'publication_details' => $details,
            ]);
        }

        return $publications->count();
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
