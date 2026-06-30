<?php

namespace App\Services;

use App\Models\WorkExperience;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;
use Throwable;

/**
 * One-off import from legacy `import_work_experiences` into `work_experiences`.
 * Resolves employees via `import_work_experiences.employee_id` → `import_employees.id`, then `import_employees.imported_id` → `employees.id`.
 *
 * Translatable columns use `_geo` (Georgian → JSON `ka`) and `_eng` (English → JSON `en`): `institution_*`, `position_*`.
 */
class WorkExperienceImportService
{
    private const IMPORT_CHUNK_SIZE = 250;

    /**
     * @return array{imported: int, skipped: int, not_found: int, failed: int}
     */
    public function importAll(bool $clearTableBefore = true): array
    {
        set_time_limit(0);

        if ($clearTableBefore) {
            $this->clearWorkExperiencesTable();
        }

        $imported = 0;
        $skipped = 0;
        $notFound = 0;
        $failed = 0;

        DB::table('import_work_experiences')->orderBy('id')->chunkById(
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
                    WorkExperience::withoutEvents(function () use (
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
                                Log::error('Work experience import failed for import row', [
                                    'import_work_experience_id' => $row->id ?? null,
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
            Log::info('Work experience import row not imported', [
                'outcome' => 'not_found',
                'reason' => 'missing_employee_id',
                'import_work_experience_id' => $row->id ?? null,
            ]);

            return;
        }

        $importEmployeeId = (int) $row->employee_id;

        if (! $importEmployeeIdToImportedId->has($importEmployeeId)) {
            $notFound++;
            Log::info('Work experience import row not imported', [
                'outcome' => 'not_found',
                'reason' => 'import_employee_id_not_found',
                'import_work_experience_id' => $row->id ?? null,
                'import_employee_id' => $importEmployeeId,
            ]);

            return;
        }

        $realEmployeeId = $importEmployeeIdToImportedId->get($importEmployeeId);
        if ($realEmployeeId === null) {
            $skipped++;
            Log::info('Work experience import row not imported', [
                'outcome' => 'skipped',
                'reason' => 'import_employee_missing_imported_id',
                'import_work_experience_id' => $row->id ?? null,
                'import_employee_id' => $importEmployeeId,
            ]);

            return;
        }

        $institution = $this->translatableFromGeoEngOrNull(
            $this->stringOrNull($row->institution_geo ?? null),
            $this->stringOrNull($row->institution_eng ?? null)
        );
        if ($institution === null) {
            $skipped++;
            Log::info('Work experience import row not imported', [
                'outcome' => 'skipped',
                'reason' => 'missing_institution',
                'import_work_experience_id' => $row->id ?? null,
                'import_employee_id' => $importEmployeeId,
                'employee_id' => (int) $realEmployeeId,
            ]);

            return;
        }

        $position = $this->translatableFromGeoEngOrNull(
            $this->stringOrNull($row->position_geo ?? null),
            $this->stringOrNull($row->position_eng ?? null)
        );
        if ($position === null) {
            $skipped++;
            Log::info('Work experience import row not imported', [
                'outcome' => 'skipped',
                'reason' => 'missing_position',
                'import_work_experience_id' => $row->id ?? null,
                'import_employee_id' => $importEmployeeId,
                'employee_id' => (int) $realEmployeeId,
            ]);

            return;
        }

        WorkExperience::query()->create([
            'employee_id' => (int) $realEmployeeId,
            'institution' => $institution,
            'position' => $position,
            'started_at' => $this->parseDateOrNull($row->started_at ?? null),
            'ended_at' => $this->parseDateOrNull($row->ended_at ?? null),
        ]);

        $imported++;
    }

    private function clearWorkExperiencesTable(): void
    {
        DB::table('work_experiences')->delete();
        DB::statement('ALTER TABLE work_experiences AUTO_INCREMENT = 1');
    }

    /**
     * Georgian (`_geo`) → `ka`, English (`_eng`) → `en`. Returns null if both are empty.
     *
     * @return array{ka: string, en: string}|null
     */
    private function translatableFromGeoEngOrNull(?string $geo, ?string $eng): ?array
    {
        if ($geo === null && $eng === null) {
            return null;
        }

        $ka = $geo ?? $eng;
        $en = $eng ?? $geo;

        return [
            'ka' => $ka,
            'en' => $en,
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

    /**
     * Returns a Y-m-d string for storage, or null for empty values and legacy MySQL zero dates (e.g. 0000-00-00).
     */
    private function parseDateOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $this->carbonToDateStringOrNull($value);
        }

        $raw = trim((string) $value);
        if ($raw === '' || $this->isLegacyZeroOrInvalidDateString($raw)) {
            return null;
        }

        try {
            $parsed = Carbon::parse($raw);

            return $this->carbonToDateStringOrNull($parsed);
        } catch (Throwable) {
            return null;
        }
    }

    private function carbonToDateStringOrNull(Carbon $value): ?string
    {
        if ((int) $value->year < 1) {
            return null;
        }

        return $value->toDateString();
    }

    /**
     * Legacy imports often contain MySQL zero dates or placeholders that must not be stored as real dates.
     */
    private function isLegacyZeroOrInvalidDateString(string $raw): bool
    {
        if (preg_match('/^0{4}-\d{2}-\d{2}/', $raw)) {
            return true;
        }

        if (preg_match('/^0{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}/', $raw)) {
            return true;
        }

        return false;
    }
}
