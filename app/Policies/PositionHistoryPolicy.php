<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PositionHistory;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PositionHistoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PositionHistory');
    }

    public function view(AuthUser $authUser, PositionHistory $positionHistory): bool
    {
        return $authUser->can('View:PositionHistory');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    /**
     * History “edit” page updates the related position; require history visibility and position update.
     */
    public function update(AuthUser $authUser, PositionHistory $positionHistory): bool
    {
        if (! $authUser->can('View:PositionHistory')) {
            return false;
        }

        $position = $positionHistory->position;

        return $position !== null && $authUser->can('update', $position);
    }

    public function delete(AuthUser $authUser, PositionHistory $positionHistory): bool
    {
        return false;
    }

    public function restore(AuthUser $authUser, PositionHistory $positionHistory): bool
    {
        return false;
    }

    public function forceDelete(AuthUser $authUser, PositionHistory $positionHistory): bool
    {
        return false;
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function replicate(AuthUser $authUser, PositionHistory $positionHistory): bool
    {
        return false;
    }

    public function reorder(AuthUser $authUser): bool
    {
        return false;
    }
}
