<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Place;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PlacePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Place');
    }

    public function view(AuthUser $authUser, Place $place): bool
    {
        return $authUser->can('View:Place');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Place');
    }

    public function update(AuthUser $authUser, Place $place): bool
    {
        return $authUser->can('Update:Place');
    }

    public function delete(AuthUser $authUser, Place $place): bool
    {
        return $authUser->can('Delete:Place');
    }

    public function restore(AuthUser $authUser, Place $place): bool
    {
        return $authUser->can('Restore:Place');
    }

    public function forceDelete(AuthUser $authUser, Place $place): bool
    {
        return $authUser->can('ForceDelete:Place');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Place');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Place');
    }

    public function replicate(AuthUser $authUser, Place $place): bool
    {
        return $authUser->can('Replicate:Place');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Place');
    }
}
