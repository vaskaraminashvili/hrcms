<?php

namespace App\Services;

use App\Enums\LanguageProficiency;
use App\Models\ForeignLanguage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;
use Throwable;

/**
 * One-off import from legacy `import_languages` into `foreign_languages`.
 * Resolves employees via `import_languages.employee_id` → `import_employees.id`, then `import_employees.imported_id` → `employees.id`.
 *
 * Legacy column `f6` holds the language name (plain string, not translatable JSON).
 * Only rows with `act = 1` are imported.
 */
class ForeignLanguageImportService
{
    private const IMPORT_CHUNK_SIZE = 250;

    /**
     * @return array{imported: int, skipped: int, not_found: int, failed: int}
     */
    public function importAll(bool $clearTableBefore = true): array
    {
        set_time_limit(0);

        if ($clearTableBefore) {
            $this->clearForeignLanguagesTable();
        }

        $imported = 0;
        $skipped = 0;
        $notFound = 0;
        $failed = 0;

        DB::table('import_languages')->orderBy('id')->chunkById(
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
                    ForeignLanguage::withoutEvents(function () use (
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
                                Log::error('Foreign language import failed for import row', [
                                    'import_language_id' => $row->id ?? null,
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

        $language = $this->stringOrNull($row->f6 ?? null);
        if ($language === null) {
            $skipped++;

            return;
        }

        $levelRaw = $this->stringOrNull($row->level ?? null);
        if ($levelRaw === null) {
            $skipped++;

            return;
        }

        $level = LanguageProficiency::tryFrom(strtoupper($levelRaw));
        if ($level === null) {
            $skipped++;

            return;
        }

        ForeignLanguage::query()->create([
            'employee_id' => (int) $realEmployeeId,
            'language' => $language,
            'level' => $level->value,
        ]);

        $imported++;
    }

    private function clearForeignLanguagesTable(): void
    {
        DB::table('foreign_languages')->delete();
        DB::statement('ALTER TABLE foreign_languages AUTO_INCREMENT = 1');
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
