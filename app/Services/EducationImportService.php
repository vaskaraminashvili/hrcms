<?php

namespace App\Services;

use App\Models\Education;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;
use Throwable;

/**
 * One-off import from legacy `import_education` into `educations`.
 * Resolves employees via `import_education.employee_id` → `import_employees.id`, then `import_employees.imported_id` → `employees.id`.
 *
 * Translatable columns: `name` / `das_eng` → `institution`, `faculty` / `fak_eng` → `program`, `specialty` / `spec_eng` → `specialty`.
 * Only rows with `act = 1` are imported. Existing rows are matched by employee + institution (ka) + started_at and updated in place.
 */
class EducationImportService
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

        DB::table('import_education')->orderBy('id')->chunkById(
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
                    Education::withoutEvents(function () use (
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
                                Log::error('Education import failed for import row', [
                                    'import_education_id' => $row->id ?? null,
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
        if ((int) ($row->act ?? 0) !== 1) {
            $skipped++;

            return;
        }

        if (! isset($row->employee_id) || $row->employee_id === '' || $row->employee_id === null) {
            $notFound++;
            Log::info('Education import row not imported', [
                'outcome' => 'not_found',
                'reason' => 'missing_employee_id',
                'import_education_id' => $row->id ?? null,
            ]);

            return;
        }

        $importEmployeeId = (int) $row->employee_id;

        if (! $importEmployeeIdToImportedId->has($importEmployeeId)) {
            $notFound++;
            Log::info('Education import row not imported', [
                'outcome' => 'not_found',
                'reason' => 'import_employee_id_not_found',
                'import_education_id' => $row->id ?? null,
                'import_employee_id' => $importEmployeeId,
            ]);

            return;
        }

        $realEmployeeId = $importEmployeeIdToImportedId->get($importEmployeeId);
        if ($realEmployeeId === null) {
            $skipped++;
            Log::info('Education import row not imported', [
                'outcome' => 'skipped',
                'reason' => 'import_employee_missing_imported_id',
                'import_education_id' => $row->id ?? null,
                'import_employee_id' => $importEmployeeId,
            ]);

            return;
        }

        $institution = $this->translatableFromGeoEngOrNull(
            $this->stringOrNull($row->name ?? null),
            $this->stringOrNull($row->das_eng ?? null)
        );
        if ($institution === null) {
            $skipped++;
            Log::info('Education import row not imported', [
                'outcome' => 'skipped',
                'reason' => 'missing_institution',
                'import_education_id' => $row->id ?? null,
                'import_employee_id' => $importEmployeeId,
                'employee_id' => (int) $realEmployeeId,
            ]);

            return;
        }

        $program = $this->translatableFromGeoEngOrNull(
            $this->stringOrNull($row->faculty ?? null),
            $this->stringOrNull($row->fak_eng ?? null)
        );
        $specialty = $this->translatableFromGeoEngOrNull(
            $this->stringOrNull($row->specialty ?? null),
            $this->stringOrNull($row->spec_eng ?? null)
        );
        $startedAt = $this->parseYearOrDateOrNull($row->from ?? null);
        $endedAt = $this->parseYearOrDateOrNull($row->till ?? null, endOfYear: true);

        $education = Education::query()
            ->where('employee_id', (int) $realEmployeeId)
            ->where('institution->ka', $institution['ka'])
            ->when(
                $startedAt !== null,
                fn ($query) => $query->where('started_at', $startedAt),
                fn ($query) => $query->whereNull('started_at')
            )
            ->first();

        $attributes = [
            'employee_id' => (int) $realEmployeeId,
            'institution' => $institution,
            'program' => $program,
            'specialty' => $specialty,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
        ];

        if ($education === null) {
            Education::query()->create($attributes);
        } else {
            $education->update($attributes);
        }

        $imported++;
    }

    /**
     * Georgian (`name`, `faculty`, `specialty`) → `ka`, English (`das_eng`, `fak_eng`, `spec_eng`) → `en`.
     * Returns null if both sides are empty.
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
     * Legacy `from` / `till` are usually four-digit years; full dates are parsed when present.
     */
    private function parseYearOrDateOrNull(mixed $value, bool $endOfYear = false): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '' || $this->isLegacyZeroOrInvalidDateString($raw)) {
            return null;
        }

        if (preg_match('/^\d{4}$/', $raw) === 1) {
            $year = (int) $raw;

            return $endOfYear
                ? sprintf('%04d-12-31', $year)
                : sprintf('%04d-01-01', $year);
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
