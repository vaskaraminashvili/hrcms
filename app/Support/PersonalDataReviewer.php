<?php

namespace App\Support;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PersonalDataReviewer
{
    public const ROLE = 'personal_data_reviewer';

    /**
     * Direct children of department 25 (ფაკულტეტები).
     *
     * @var list<int>
     */
    public const FACULTY_DEPARTMENT_IDS = [72, 73, 74, 75, 76, 77, 78, 271, 272];

    /** @var array<string, Collection<int, int>> */
    private static array $departmentIds = [];

    public static function isReviewer(mixed $user): bool
    {
        return $user instanceof User && $user->hasRole(self::ROLE);
    }

    /**
     * @return Collection<int, int>
     */
    public static function departmentIdsFor(User $user): Collection
    {
        $cacheKey = $user->getKey().':'.$user->department_id;

        if (array_key_exists($cacheKey, self::$departmentIds)) {
            return self::$departmentIds[$cacheKey];
        }

        $department = $user->department;

        if ($department === null) {
            return self::$departmentIds[$cacheKey] = collect();
        }

        return self::$departmentIds[$cacheKey] = $department->descendants()->pluck('id')->map(fn (mixed $id): int => (int) $id)->values();
    }

    /**
     * @param  Builder<Employee>  $query
     * @return Builder<Employee>
     */
    public static function scopeEmployees(Builder $query, User $user): Builder
    {
        return $query;
    }

    public static function employeeIsInScope(User $user, Employee $employee): bool
    {
        return self::scopeEmployees(
            Employee::query()->whereKey($employee->getKey()),
            $user,
        )->exists();
    }
}
