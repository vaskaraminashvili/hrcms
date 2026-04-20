<?php

namespace App\Services;

use App\Models\ScholarshipAward;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;
use Throwable;

/**
 * One-off import from legacy `import_scholarshipaward` into `scholarships_awards`.
 * Resolves employees via `import_scholarshipaward.employee_id` → `import_employees.id`, then `import_employees.imported_id` → `employees.id`.
 *
 * Translatable columns: `topic_geo` / `topic_eng` → `title`, `institution_geo` / `institution_eng` → `issuer` (Georgian → `ka`, English → `en`).
 */
class ScholarshipAwardImportService
{
    private const IMPORT_CHUNK_SIZE = 250;

    /**
     * @return array{imported: int, skipped: int, not_found: int, failed: int}
     */
    public function importAll(bool $clearTableBefore = true): array
    {
        set_time_limit(0);

        if ($clearTableBefore) {
            $this->clearScholarshipsAwardsTable();
        }

        $imported = 0;
        $skipped = 0;
        $notFound = 0;
        $failed = 0;

        DB::table('import_scholarshipaward')->orderBy('id')->chunkById(
            self::IMPORT_CHUNK_SIZE,
            function (Collection $rows) use (&$imported, &$skipped, &$notFound, &$failed): void {
                $employeeIds = $rows
                    ->pluck('employee_id')
                    ->filter(fn (mixed $id): bool => $id !== null && $id !== '')
                    ->map(fn (mixed $id): int => (int) $id)
                    ->unique()
                    ->values();

                /** @var Collection<int, int|null> $importEmployeeIdToImportedId */
                $importEmployeeIdToImportedId = $employeeIds->isEmpty()
                    ? collect()
                    : DB::table('import_employees')
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
                    ScholarshipAward::withoutEvents(function () use (
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
                                Log::error('Scholarship award import failed for import row', [
                                    'import_scholarshipaward_id' => $row->id ?? null,
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
            Log::info('Scholarship award import not found: no employee_id on legacy row', [
                'outcome' => 'not_found',
                'reason' => 'missing_employee_id',
                'detail' => 'import_scholarshipaward.employee_id is null or empty.',
                'import_scholarshipaward_id' => $row->id ?? null,
            ]);

            return;
        }

        $importEmployeeId = (int) $row->employee_id;

        if (! $importEmployeeIdToImportedId->has($importEmployeeId)) {
            $notFound++;
            Log::info('Scholarship award import not found: no import_employees row for FK', [
                'outcome' => 'not_found',
                'reason' => 'import_employee_id_not_found',
                'detail' => 'employee_id does not exist in import_employees (or chunk resolution missed it).',
                'import_scholarshipaward_id' => $row->id ?? null,
                'import_employee_id' => $importEmployeeId,
            ]);

            return;
        }

        $realEmployeeId = $importEmployeeIdToImportedId->get($importEmployeeId);
        if ($realEmployeeId === null) {
            $skipped++;
            Log::info('Scholarship award import skipped: import_employees.imported_id is null', [
                'outcome' => 'skipped',
                'reason' => 'import_employee_missing_imported_id',
                'detail' => 'Legacy employee row exists but was never linked to employees (import_employees.imported_id is null).',
                'import_scholarshipaward_id' => $row->id ?? null,
                'import_employee_id' => $importEmployeeId,
            ]);

            return;
        }

        $topicGeoRaw = $row->topic_geo ?? null;
        $topicEngRaw = $row->topic_eng ?? null;
        $title = $this->translatableFromGeoEngOrNull(
            $this->stringOrNull($topicGeoRaw),
            $this->stringOrNull($topicEngRaw)
        );
        if ($title === null) {
            $skipped++;
            Log::info('Scholarship award import skipped: empty topic (title)', [
                'outcome' => 'skipped',
                'reason' => 'missing_topic',
                'detail' => 'Both topic_geo and topic_eng are missing, null, or whitespace-only after trim.',
                'import_scholarshipaward_id' => $row->id ?? null,
                'import_employee_id' => $importEmployeeId,
                'employee_id' => (int) $realEmployeeId,
                'topic_geo' => $this->logSnippet($topicGeoRaw),
                'topic_eng' => $this->logSnippet($topicEngRaw),
            ]);

            return;
        }

        $attributes = [
            'employee_id' => (int) $realEmployeeId,
            'title' => $title,
            'issued_at' => $this->parseDateOrNull($row->issued_at ?? $row->date ?? null),
        ];

        $issuer = $this->translatableFromGeoEngOrNull(
            $this->stringOrNull($row->institution_geo ?? null),
            $this->stringOrNull($row->institution_eng ?? null)
        );
        if ($issuer !== null) {
            $attributes['issuer'] = $issuer;
        }

        ScholarshipAward::query()->create($attributes);

        $imported++;
    }

    private function clearScholarshipsAwardsTable(): void
    {
        DB::table('scholarships_awards')->delete();
        DB::statement('ALTER TABLE scholarships_awards AUTO_INCREMENT = 1');
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
     * Short preview for logs (avoids huge lines; shows null vs empty string).
     */
    private function logSnippet(mixed $value, int $maxLength = 160): ?string
    {
        if ($value === null) {
            return null;
        }

        $s = (string) $value;
        if (mb_strlen($s) <= $maxLength) {
            return $s;
        }

        return mb_substr($s, 0, $maxLength).'…';
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
