<?php

namespace App\Services;

use App\Enums\EmployeeStatusEnum;
use App\Enums\Gender;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * Temporary one-off import from legacy `import_employees` into `employees`.
 */
class EmployeeImportService
{
    private const PLACEHOLDER_BIRTH_DATE = '1800-01-01';

    private const PERSONAL_NUMBER_LENGTH = 11;

    private const IMPORT_CHUNK_SIZE = 250;

    /**
     * Import all rows from `import_employees`. Uses `personal_number` as the natural key (updates existing).
     * Missing personal numbers, values longer than 11 characters (DB limit), or non-fitting IDs get synthetic values
     * `00000000001`, `00000000002`, … (first free 11-digit slot). Shorter all-digit values are left-padded with zeros to length 11.
     * Missing or invalid birth dates use {@see PLACEHOLDER_BIRTH_DATE}.
     *
     * @param  bool  $clearTableBefore  When true, deletes all rows from `employees` first (child rows cascade). Default true.
     * @return array{imported: int, skipped: int}
     */
    public function importAll(bool $clearTableBefore = true): array
    {
        set_time_limit(0);

        if ($clearTableBefore) {
            $this->clearEmployeesTable();
        }

        $imported = 0;

        $usedPersonalNumbers = $this->loadUsedPersonalNumbers();

        DB::table('import_employees')->orderBy('id')->chunkById(
            self::IMPORT_CHUNK_SIZE,
            function ($rows) use (&$imported, &$usedPersonalNumbers) {
                DB::transaction(function () use ($rows, &$imported, &$usedPersonalNumbers) {
                    Employee::withoutEvents(function () use ($rows, &$imported, &$usedPersonalNumbers) {
                        foreach ($rows as $row) {
                            $this->importRow($row, $usedPersonalNumbers);
                            $imported++;
                        }
                    });
                });
            }
        );

        return ['imported' => $imported, 'skipped' => 0];
    }

    /**
     * Hard-delete all employees. Related rows with ON DELETE CASCADE are removed by MySQL.
     */
    private function clearEmployeesTable(): void
    {
        DB::table('employees')->delete();
    }

    /**
     * @param  array<string, true>  $usedPersonalNumbers
     */
    private function importRow(stdClass $row, array &$usedPersonalNumbers): void
    {
        $personalNumber = $this->resolvePersonalNumber($row->personal_number ?? null, $usedPersonalNumbers);
        $birthDate = $this->resolveBirthDate($row->birth_date ?? null);

        $addressDetails = [
            'address_physical' => $this->stringOrNull($row->address ?? null) ?? '',
            'address_jurisdiction' => $this->stringOrNull($row->pysical_address ?? null) ?? '',
            'en_address_physical' => $this->stringOrNull($row->address_eng ?? null) ?? '',
            'en_address_jurisdiction' => '',
        ];

        $employee = Employee::query()->firstOrNew(['personal_number' => $personalNumber]);

        $employee->fill([
            'name' => $this->stringOrNull($row->name ?? null) ?? '',
            'surname' => $this->stringOrNull($row->surname ?? null) ?? '',
            'name_eng' => $this->stringOrNull($row->name_eng ?? null),
            'surrname_eng' => $this->stringOrNull($row->surname_eng ?? null),
            'email' => $this->stringOrNull($row->email ?? null),
            'birth_date' => $birthDate,
            'gender' => Gender::tryFrom((string) ($row->gender ?? '')),
            'education' => isset($row->education) ? (int) $row->education : null,
            'degree' => $this->stringOrNull($row->degree ?? null),
            'mobile_number' => $this->stringOrNull($row->tel_number ?? null),
            'account_number' => $this->stringOrNull($row->bank_acount_number ?? null),
            'address_details' => $addressDetails,
            'citizenship' => null,
        ]);

        $employee->status = $this->mapImportStatus($row->arq ?? null);
        $employee->save();
    }

    /**
     * @return array<string, true>
     */
    private function loadUsedPersonalNumbers(): array
    {
        $used = [];
        foreach (Employee::query()->pluck('personal_number') as $pn) {
            if ($pn !== null && $pn !== '') {
                $used[(string) $pn] = true;
            }
        }

        return $used;
    }

    /**
     * @param  array<string, true>  $usedPersonalNumbers
     */
    private function resolvePersonalNumber(?string $fromImport, array &$usedPersonalNumbers): string
    {
        $trimmed = $this->stringOrNull($fromImport);
        if ($trimmed === null) {
            return $this->allocateSyntheticPersonalNumber($usedPersonalNumbers);
        }

        if (strlen($trimmed) > self::PERSONAL_NUMBER_LENGTH) {
            return $this->allocateSyntheticPersonalNumber($usedPersonalNumbers);
        }

        if (ctype_digit($trimmed) && strlen($trimmed) < self::PERSONAL_NUMBER_LENGTH) {
            return str_pad($trimmed, self::PERSONAL_NUMBER_LENGTH, '0', STR_PAD_LEFT);
        }

        return $trimmed;
    }

    /**
     * @param  array<string, true>  $usedPersonalNumbers
     */
    private function allocateSyntheticPersonalNumber(array &$usedPersonalNumbers): string
    {
        $sequence = 1;
        while (true) {
            $candidate = str_pad((string) $sequence, self::PERSONAL_NUMBER_LENGTH, '0', STR_PAD_LEFT);
            $sequence++;
            if (! isset($usedPersonalNumbers[$candidate])) {
                $usedPersonalNumbers[$candidate] = true;

                return $candidate;
            }
        }
    }

    private function resolveBirthDate(mixed $value): string
    {
        $normalized = $this->normalizeBirthDate($value);

        return $normalized ?? self::PLACEHOLDER_BIRTH_DATE;
    }

    private function mapImportStatus(mixed $status): EmployeeStatusEnum
    {
        if ($status === 0 || $status === '0') {
            return EmployeeStatusEnum::ACTIVE;
        }

        return EmployeeStatusEnum::ARCHIVED;
    }

    /**
     * Returns null for empty, MySQL zero-dates, or unparseable values (caller uses placeholder).
     */
    private function normalizeBirthDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '' || preg_match('/^0000-00-00/', $raw) === 1) {
            return null;
        }

        try {
            $date = Carbon::parse($raw);
        } catch (\Throwable) {
            return null;
        }

        if ($date->year < 1) {
            return null;
        }

        return $date->format('Y-m-d');
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
