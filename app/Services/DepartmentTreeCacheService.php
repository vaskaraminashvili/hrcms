<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class DepartmentTreeCacheService
{
    private const string CACHE_KEY = 'departments.tree.records';

    /**
     * @return Collection<int, Department>
     */
    public function getRecords(): Collection
    {
        /** @var Collection<int, Department> */
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn (): Collection => Department::query()
                ->orderBy('parent_id')
                ->orderBy('order')
                ->with(['children', 'parent'])
                ->get(),
        );
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
