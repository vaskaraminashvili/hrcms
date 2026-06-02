<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * One-off import from legacy `import_work_experiences` into `work_experiences`.
 * Resolves employees via `import_work_experiences.employee_id` → `import_employees.id`, then `import_employees.imported_id` → `employees.id`.
 *
 * Translatable columns use `_geo` (Georgian → JSON `ka`) and `_eng` (English → JSON `en`): `institution_*`, `position_*`.
 */
class EmployeeImageImportService
{
    private const IMPORT_CHUNK_SIZE = 250;

    /**
     * @return array{imported: int, skipped: int, not_found: int, failed: int}
     */
    public function importAll(): array
    {
        set_time_limit(0);

        $imported = 0;
        $skipped = 0;
        $notFound = 0;
        $failed = 0;

        DB::table('import_employees')
            ->whereNotNull('imported_id')
            ->whereNotNull('photo')
            ->orderBy('id')->chunkById(
                self::IMPORT_CHUNK_SIZE,
                function (Collection $rows) use (&$imported, &$skipped, &$notFound, &$failed): void {
                    foreach ($rows as $row) {
                        $employeeId = isset($row->imported_id) ? (int) $row->imported_id : null;
                        $photo = isset($row->photo) ? trim((string) $row->photo) : null;

                        if ($employeeId === null || $employeeId <= 0) {
                            $notFound++;

                            continue;
                        }

                        if ($photo === null || $photo === '') {
                            $skipped++;

                            continue;
                        }

                        try {
                            $updatedRows = DB::table('employees')
                                ->where('id', $employeeId)
                                ->update([
                                    'photo' => $photo,
                                    'updated_at' => now(),
                                ]);

                            if ($updatedRows === 0) {
                                $notFound++;

                                continue;
                            }

                            $imported++;
                        } catch (Throwable) {
                            $failed++;
                        }
                    }
                }
            );

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'not_found' => $notFound,
            'failed' => $failed,
        ];
    }
}
