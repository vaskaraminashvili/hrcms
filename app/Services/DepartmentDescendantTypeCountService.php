<?php

namespace App\Services;

use App\Enums\DepartmentType;
use App\Filament\Resources\Departments\Fields\DepartmentTextField;
use App\Models\Department;
use Illuminate\Support\Facades\Cache;

class DepartmentDescendantTypeCountService
{
    private const int CACHE_TTL_SECONDS = 900;

    /**
     * Cached payload for the HTTP endpoint and Filament tree (matches prior controller behavior).
     *
     * @return array<int, array{label: string, count: int, classes: string}>
     */
    public function getCachedDescendantTypeCountsPayload(Department $department): array
    {
        return Cache::remember(
            $this->cacheKey($department),
            now()->addSeconds(self::CACHE_TTL_SECONDS),
            fn (): array => $this->descendantTypeCountsPayload($department),
        );
    }

    /**
     * Uncached descendant type counts (e.g. tests).
     *
     * @return array<int, array{label: string, count: int, classes: string}>
     */
    public function descendantTypeCountsPayload(Department $department): array
    {
        return $department->descendants()
            ->whereNotNull('type')
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->get()
            ->map(function (Department $row): array {
                $typeValue = $row->type instanceof \BackedEnum
                    ? $row->type->value
                    : (string) $row->type;

                $enum = DepartmentType::from($typeValue);
                $colorKey = $enum->getColor() ?? 'gray';

                $classes = DepartmentTextField::BADGE_COLOR_CLASSES[$colorKey]
                    ?? DepartmentTextField::BADGE_COLOR_CLASSES['gray'];

                return [
                    'label' => $enum->getLabel(),
                    'count' => (int) $row->total,
                    'classes' => $classes,
                ];
            })
            ->filter(fn (array $item): bool => $item['count'] > 0)
            ->values()
            ->all();
    }

    private function cacheKey(Department $department): string
    {
        return sprintf('departments.%d.descendant_type_counts', $department->getKey());
    }
}
