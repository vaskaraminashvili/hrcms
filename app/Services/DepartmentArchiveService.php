<?php

namespace App\Services;

use App\Enums\DepartmentStatus;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepartmentArchiveService
{
    public function archiveAndReplicate(Department $original, array $newData): Department
    {
        try {
            return DB::transaction(function () use ($original, $newData) {
                return $this->archiveAndReplicateRecursive($original, null, $newData);
            });
        } catch (\Throwable $e) {
            Log::error('Department archive and replicate failed', [
                'department_id' => $original->id,
                'department_name' => $original->name,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Recursively archive and replicate a department and all its descendants.
     *
     * @param  array<string, mixed>|null  $overrides  Name/parent overrides for the root department only
     */
    private function archiveAndReplicateRecursive(Department $original, ?Department $newParent, ?array $overrides = null): Department
    {
        $children = $original->children()->get();

        $newDepartment = $original->replicate();
        if ($overrides !== null) {
            $newDepartment->name = $overrides['name'];
            $newDepartment->slug = $this->makeUniqueSlug(str($overrides['name'])->slug()->toString());
            $newDepartment->parent_id = $overrides['parent_id'] ?? $original->parent_id;
        } else {
            $newDepartment->slug = $this->makeUniqueSlug(str($original->name)->slug()->toString());
            $newDepartment->parent_id = $newParent->id;
        }
        $newDepartment->status = DepartmentStatus::ACTIVE->value;
        $newDepartment->order = $original->order;
        $newDepartment->save();

        if ($original->positions->isNotEmpty()) {
            foreach ($original->positions as $position) {
                $newPosition = $position->replicate();
                $newPosition->department_id = $newDepartment->id;
                $newPosition->status = $position->status;
                $newPosition->save();
            }
            $original->positions()->update([
                'status' => DepartmentStatus::ARCHIVED->value,
                'date_end' => now(),
            ]);
        }

        $original->update([
            'status' => DepartmentStatus::ARCHIVED->value,
        ]);

        foreach ($children as $child) {
            $this->archiveAndReplicateRecursive($child, $newDepartment, null);
        }

        return $newDepartment;
    }

    private function makeUniqueSlug(string $baseSlug): string
    {
        $slug = $baseSlug;
        $suffix = 0;

        while (Department::where('slug', $slug)->exists()) {
            $suffix++;
            $slug = $baseSlug.'-'.$suffix;
        }

        return $slug;
    }
}
