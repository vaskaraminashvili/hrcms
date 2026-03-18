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
                // 1. Replicate department with new data
                $newDepartment = $original->replicate();
                $newDepartment->name = $newData['name'];
                $newDepartment->slug = str($newData['name'])->slug();
                $newDepartment->parent_id = $newData['parent_id'] ?? $original->parent_id;
                $newDepartment->status = DepartmentStatus::ACTIVE->value;
                $newDepartment->save();

                // 2. Replicate positions to new department (if any exist)
                if ($original->positions->isNotEmpty()) {
                    foreach ($original->positions as $position) {
                        $newPosition = $position->replicate();
                        $newPosition->department_id = $newDepartment->id;
                        $newPosition->status = $position->status; // keep original status
                        $newPosition->save();
                    }

                    // 3. Archive original positions
                    $original->positions()->update([
                        'status' => DepartmentStatus::ARCHIVED->value,
                    ]);
                }

                // 4. Archive original department
                $original->update([
                    'status' => DepartmentStatus::ARCHIVED->value,
                ]);

                return $newDepartment;
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
}
