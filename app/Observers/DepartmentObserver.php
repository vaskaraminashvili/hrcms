<?php

namespace App\Observers;

use App\Models\Department;
use App\Services\DepartmentTreeCacheService;

class DepartmentObserver
{
    public function saved(Department $department): void
    {
        app(DepartmentTreeCacheService::class)->forget();
    }

    public function deleted(Department $department): void
    {
        app(DepartmentTreeCacheService::class)->forget();
    }
}
