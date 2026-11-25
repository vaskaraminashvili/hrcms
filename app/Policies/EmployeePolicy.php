<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use App\Support\PersonalDataReviewer;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class EmployeePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        $reviewer = $this->personalDataReviewer($authUser);

        if ($reviewer !== null) {
            return $reviewer->department_id !== null;
        }

        return $authUser->can('ViewAny:Employee');
    }

    public function view(AuthUser $authUser, Employee $employee): bool
    {
        return $authUser->can('View:Employee');
    }

    public function viewCv(AuthUser $authUser, Employee $employee): bool
    {
        $reviewer = $this->personalDataReviewer($authUser);

        if ($reviewer !== null) {
            return PersonalDataReviewer::employeeIsInScope($reviewer, $employee);
        }

        if ($authUser->can('View:Employee')) {
            return true;
        }

        return (int) $employee->user_id === (int) $authUser->getAuthIdentifier();
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Employee');
    }

    public function update(AuthUser $authUser, Employee $employee): bool
    {
        $reviewer = $this->personalDataReviewer($authUser);

        if ($reviewer !== null) {
            return PersonalDataReviewer::employeeIsInScope($reviewer, $employee);
        }

        return $authUser->can('Update:Employee');
    }

    public function importPersonalFile(AuthUser $authUser, Employee $employee): bool
    {
        $reviewer = $this->personalDataReviewer($authUser);

        if ($reviewer !== null) {
            return PersonalDataReviewer::employeeIsInScope($reviewer, $employee);
        }

        if ($authUser->can('Update:Employee')) {
            return true;
        }

        return $authUser->hasRole('employee')
            && (int) $employee->user_id === (int) $authUser->getAuthIdentifier();
    }

    public function delete(AuthUser $authUser, Employee $employee): bool
    {
        return $authUser->can('Delete:Employee');
    }

    public function restore(AuthUser $authUser, Employee $employee): bool
    {
        return $authUser->can('Restore:Employee');
    }

    public function forceDelete(AuthUser $authUser, Employee $employee): bool
    {
        return $authUser->can('ForceDelete:Employee');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Employee');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Employee');
    }

    public function replicate(AuthUser $authUser, Employee $employee): bool
    {
        return $authUser->can('Replicate:Employee');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Employee');
    }

    private function personalDataReviewer(AuthUser $authUser): ?User
    {
        if (! $authUser instanceof User || ! PersonalDataReviewer::isReviewer($authUser)) {
            return null;
        }

        return $authUser;
    }
}
