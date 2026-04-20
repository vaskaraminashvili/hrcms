<?php

namespace App\Services;

use App\Enums\AcademicPosition as AcademicPositionEnum;
use App\Models\AcademicPosition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;
use Throwable;

/**
 * One-off import from legacy `import_academic_positions` into `academic_positions`.
 * Resolves employees via `import_academic_positions.employee_id` → `import_employees.id`, then `import_employees.imported_id` → `employees.id`.
 *
 * Title values may be enum codes (ASSISTANT, …) or English labels (Assistant Professor, …).
 */
class AcademicPositionImportService
{
    private const IMPORT_CHUNK_SIZE = 250;

    /**
     * @return array{imported: int, skipped: int, not_found: int, failed: int}
     */
    public function importAll(bool $clearTableBefore = true): array
    {
        set_time_limit(0);

        if ($clearTableBefore) {
            $this->clearAcademicPositionsTable();
        }

        $imported = 0;
        $skipped = 0;
        $notFound = 0;
        $failed = 0;

        DB::table('import_academic_positions')->orderBy('id')->chunkById(
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
                    AcademicPosition::withoutEvents(function () use (
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
                                Log::error('Academic position import failed for import row', [
                                    'import_academic_position_id' => $row->id ?? null,
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

        $titleRaw = $this->stringOrNull($row->title ?? null);
        $titleValue = $this->resolveTitleEnumValue($titleRaw);
        if ($titleValue === null) {
            $skipped++;

            return;
        }

        AcademicPosition::query()->create([
            'employee_id' => (int) $realEmployeeId,
            'title' => $titleValue,
        ]);

        $imported++;
    }

    private function clearAcademicPositionsTable(): void
    {
        DB::table('academic_positions')->delete();
        DB::statement('ALTER TABLE academic_positions AUTO_INCREMENT = 1');
    }

    private function resolveTitleEnumValue(?string $raw): ?string
    {
        $normalized = $this->stringOrNull($raw);
        if ($normalized === null) {
            return null;
        }

        $asEnum = AcademicPositionEnum::tryFrom(strtoupper($normalized));
        if ($asEnum !== null) {
            return $asEnum->value;
        }

        $lower = strtolower($normalized);

        $fromEnglish = match ($lower) {
            'assistant professor' => AcademicPositionEnum::ASSISTANT->value,
            'associate professor' => AcademicPositionEnum::ASSOCIATED->value,
            'professor' => AcademicPositionEnum::PROFESSOR->value,
            default => null,
        };

        if ($fromEnglish !== null) {
            return $fromEnglish;
        }

        foreach (AcademicPositionEnum::cases() as $case) {
            if ($normalized === $case->getLabel()) {
                return $case->value;
            }
        }

        return null;
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
