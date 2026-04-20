<?php

namespace App\Services;

use App\Models\ComputerSkill;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;
use Throwable;

/**
 * One-off import from legacy `import_computer_skills` into `computer_skills`.
 * Resolves employees via `import_computer_skills.employee_id` → `import_employees.id`, then `import_employees.imported_id` → `employees.id`.
 */
class ComputerSkillImportService
{
    private const IMPORT_CHUNK_SIZE = 250;

    /**
     * @return array{imported: int, skipped: int, not_found: int, failed: int}
     */
    public function importAll(bool $clearTableBefore = true): array
    {
        set_time_limit(0);

        if ($clearTableBefore) {
            $this->clearComputerSkillsTable();
        }

        $imported = 0;
        $skipped = 0;
        $notFound = 0;
        $failed = 0;

        DB::table('import_computer_skills')->orderBy('id')->chunkById(
            self::IMPORT_CHUNK_SIZE,
            function (Collection $rows) use (&$imported, &$skipped, &$notFound, &$failed): void {
                $employeeIds = $rows
                    ->pluck('employee_id')
                    ->filter(fn (mixed $id): bool => $id !== null && $id !== '')
                    ->map(fn (mixed $id): int => (int) $id)
                    ->unique()
                    ->values();

                if ($employeeIds->isEmpty()) {
                    $notFound += $rows->count();

                    return;
                }

                /** @var Collection<int, int|null> $importEmployeeIdToImportedId */
                $importEmployeeIdToImportedId = DB::table('import_employees')
                    ->whereIn('id', $employeeIds->all())
                    ->pluck('imported_id', 'id')
                    ->mapWithKeys(fn (?int $importedId, int|string $id): array => [(int) $id => $importedId]);

                DB::transaction(function () use (
                    $rows,
                    $importEmployeeIdToImportedId,
                    &$imported,
                    &$skipped,
                    &$notFound,
                    &$failed
                ): void {
                    ComputerSkill::withoutEvents(function () use (
                        $rows,
                        $importEmployeeIdToImportedId,
                        &$imported,
                        &$skipped,
                        &$notFound,
                        &$failed
                    ): void {
                        foreach ($rows as $row) {
                            try {
                                $this->importOneRow(
                                    $row,
                                    $importEmployeeIdToImportedId,
                                    $imported,
                                    $skipped,
                                    $notFound
                                );
                            } catch (Throwable $e) {
                                $failed++;
                                Log::error('Computer skill import failed for import row', [
                                    'import_computer_skill_id' => $row->id ?? null,
                                    'exception' => $e->getMessage(),
                                ]);
                            }
                        }
                    });
                });
            }
        );

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'not_found' => $notFound,
            'failed' => $failed,
        ];
    }

    /**
     * @param  Collection<int, int|null>  $importEmployeeIdToImportedId
     */
    private function importOneRow(
        stdClass $row,
        Collection $importEmployeeIdToImportedId,
        int &$imported,
        int &$skipped,
        int &$notFound
    ): void {
        if (! isset($row->employee_id) || $row->employee_id === '' || $row->employee_id === null) {
            $notFound++;

            return;
        }

        $importEmployeeId = (int) $row->employee_id;

        if (! $importEmployeeIdToImportedId->has($importEmployeeId)) {
            $notFound++;

            return;
        }

        $realEmployeeId = $importEmployeeIdToImportedId->get($importEmployeeId);
        if ($realEmployeeId === null) {
            $skipped++;

            return;
        }

        $title = $this->stringOrNull($row->title ?? null);
        if ($title === null) {
            $skipped++;

            return;
        }

        $levelRaw = $this->stringOrNull($row->level ?? null);
        $level = $levelRaw ?? '—';

        ComputerSkill::query()->create([
            'employee_id' => (int) $realEmployeeId,
            'title' => $this->toTranslatableJson($title),
            'level' => $this->toTranslatableJson($level),
        ]);

        $imported++;
    }

    private function clearComputerSkillsTable(): void
    {
        DB::table('computer_skills')->delete();
        DB::statement('ALTER TABLE computer_skills AUTO_INCREMENT = 1');
    }

    /**
     * @return array{ka: string, en: string}
     */
    private function toTranslatableJson(string $value): array
    {
        return [
            'ka' => $value,
            'en' => $value,
        ];
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
