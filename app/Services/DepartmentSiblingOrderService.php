<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;

/**
 * Ensures assigning an {@see Department::$order} under a {@see Department::$parent_id}
 * shifts other siblings downward (increment) so slots stay unique within the sibling set.
 */
class DepartmentSiblingOrderService
{
    /**
     * @param  int|null  $parentId  Target parent (null for root siblings)
     */
    public function shiftSiblingsOpeningSlot(?int $parentId, int $desiredOrder, ?int $excludeDepartmentId): void
    {
        Department::query()
            ->where('parent_id', $parentId)
            ->when(
                $excludeDepartmentId !== null,
                fn (Builder $query): Builder => $query->whereKeyNot($excludeDepartmentId),
            )
            ->where('order', '>=', $desiredOrder)
            ->increment('order');

        app(DepartmentTreeCacheService::class)->forget();
    }

    /**
     * When creating, always reshuffle siblings at and after {@see $newOrder}.
     *
     * When updating, reshuffle only if the sibling group or position actually changed — otherwise unchanged
     * rows would incorrectly be incremented (e.g. editing name-only while sharing order with others).
     */
    public function shouldApplyShiftForSave(
        mixed $oldParentId,
        int $oldOrder,
        mixed $newParentId,
        int $newOrder,
        bool $isCreating,
    ): bool {
        if ($isCreating) {
            return true;
        }

        $oldParentNormalized = self::normalizeSiblingParentKey($oldParentId);
        $newParentNormalized = self::normalizeSiblingParentKey($newParentId);

        return $oldParentNormalized !== $newParentNormalized || $oldOrder !== $newOrder;
    }

    public static function normalizeSiblingParentKey(mixed $parentId): ?int
    {
        if ($parentId === null || $parentId === '') {
            return null;
        }

        return (int) $parentId;
    }
}
