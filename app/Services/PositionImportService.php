<?php

namespace App\Services;

use App\Enums\DepartmentStatus;
use App\Enums\PositionStatus;
use App\Enums\PositionType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Place;
use App\Models\Position;
use App\Models\VacationPolicy;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use stdClass;

/**
 * One-off import from legacy `import_*` tables into `positions`.
 * Maps local employees (personal_number); creates missing departments/places by name when needed.
 */
class PositionImportService
{
    private const IMPORT_CHUNK_SIZE = 250;

    private const PERSONAL_NUMBER_LENGTH = 11;

    private const PLACEHOLDER_DEPARTMENT_NAME = '[Import] Unknown department';

    private const PLACEHOLDER_PLACE_NAME = '[Import] Unknown place';

    private const FALLBACK_POSITION_TYPE = PositionType::AdministrativePersonnel;

    /**
     * @return array{
     *     imported: int,
     *     skipped: int,
     *     skip_reasons: array<string, int>,
     *     departments_created: int,
     *     places_created: int,
     *     position_type_fallbacks: int,
     * }
     */
    public function importAll(bool $clearTableBefore = true): array
    {
        set_time_limit(0);

        if ($clearTableBefore) {
            $this->clearPositionsTable();
        }

        $places = Place::query()->pluck('id', 'name');
        $departments = Department::query()->pluck('id', 'name');
        $employees = Employee::query()->pluck('id', 'personal_number');
        $vacationPolicyIds = VacationPolicy::query()
            ->get()
            ->mapWithKeys(fn (VacationPolicy $policy) => [$policy->position_type->value => $policy->id]);

        $imported = 0;
        $skipped = 0;
        /** @var array<string, int> */
        $skipReasons = [];
        $departmentsCreated = 0;
        $placesCreated = 0;
        $positionTypeFallbacks = 0;

        Log::info('Position import started', [
            'clear_table_before' => $clearTableBefore,
        ]);

        DB::table('import_positions')
            ->join('import_employees', 'import_positions.employee_id', '=', 'import_employees.id')
            ->join('import_departments', 'import_positions.department_id', '=', 'import_departments.id')
            ->join('import_places', 'import_positions.place_id', '=', 'import_places.id')
            ->select([
                'import_positions.id',
                'import_employees.imported_id as employee_id',
                'import_positions.department_id',
                'import_positions.place_id',
                'import_positions.position_type',
                'import_positions.date_start',
                'import_positions.date_end',
                'import_positions.status',
                'import_positions.act_number',
                'import_positions.act_date',
                'import_positions.staff_type',
                'import_positions.clinical',
                'import_positions.clinical_text',
                'import_positions.salary',
                'import_employees.personal_number as employee_personal_number',
                'import_departments.sax_geo as department_name',
                'import_places.tanamd as place_name',
            ])
            ->orderBy('import_positions.employee_id')
            ->orderBy('import_positions.date_start')
            ->chunkById(
                self::IMPORT_CHUNK_SIZE,
                function (Collection $rows) use (
                    $places,
                    $departments,
                    $employees,
                    $vacationPolicyIds,
                    &$imported,
                    &$skipped,
                    &$skipReasons,
                    &$departmentsCreated,
                    &$placesCreated,
                    &$positionTypeFallbacks
                ): void {
                    DB::transaction(function () use (
                        $rows,
                        $places,
                        $departments,
                        $employees,
                        $vacationPolicyIds,
                        &$imported,
                        &$skipped,
                        &$skipReasons,
                        &$departmentsCreated,
                        &$placesCreated,
                        &$positionTypeFallbacks
                    ): void {
                        foreach ($rows as $row) {
                            $result = $this->importRow(
                                $row,
                                $places,
                                $departments,
                                $employees,
                                $vacationPolicyIds,
                                $departmentsCreated,
                                $placesCreated,
                                $positionTypeFallbacks
                            );
                            if ($result === true) {
                                $imported++;
                            } else {
                                $skipped++;
                                $reason = is_string($result) ? $result : 'unknown';
                                $skipReasons[$reason] = ($skipReasons[$reason] ?? 0) + 1;
                            }
                        }
                    });
                },
                'import_positions.id',
                'id'
            );

        Log::info('Position import finished', [
            'imported' => $imported,
            'skipped' => $skipped,
            'skip_reasons' => $skipReasons,
            'departments_created' => $departmentsCreated,
            'places_created' => $placesCreated,
            'position_type_fallbacks' => $positionTypeFallbacks,
        ]);

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'skip_reasons' => $skipReasons,
            'departments_created' => $departmentsCreated,
            'places_created' => $placesCreated,
            'position_type_fallbacks' => $positionTypeFallbacks,
        ];
    }

    /**
     * @param  Collection<string, int>  $places  name => id (mutated when new places are created)
     * @param  Collection<string, int>  $departments  name => id (mutated when new departments are created)
     * @param  Collection<string, int>  $employees  personal_number => id
     * @param  Collection<string, int>  $vacationPolicyIds  position_type value => id
     * @return true|string True on success, or a skip-reason key string
     */
    private function importRow(
        stdClass $row,
        Collection $places,
        Collection $departments,
        Collection $employees,
        Collection $vacationPolicyIds,
        int &$departmentsCreated,
        int &$placesCreated,
        int &$positionTypeFallbacks
    ): bool|string {
        $importPositionId = (int) $row->id;

        $personalKey = $this->normalizePersonalNumberForLookup($row->employee_personal_number ?? null);
        if ($personalKey === null) {
            Log::warning('Position import skipped: invalid or empty personal_number', [
                'import_position_id' => $importPositionId,
                'raw_personal_number' => $row->employee_personal_number ?? null,
            ]);

            return 'invalid_personal_number';
        }

        $employeeId = $employees->get($personalKey);
        if ($employeeId === null) {
            Log::warning('Position import skipped: employee not found for personal_number', [
                'import_position_id' => $importPositionId,
                'personal_number' => $personalKey,
            ]);

            return 'employee_not_found';
        }

        $departmentId = $this->resolveDepartmentId(
            $this->stringOrNull($row->department_name ?? null),
            $departments,
            $departmentsCreated,
            $importPositionId
        );

        $placeId = $this->resolvePlaceId(
            $this->stringOrNull($row->place_name ?? null),
            $places,
            $placesCreated,
            $importPositionId
        );

        $rawPositionType = (string) ($row->position_type ?? '');
        $positionType = PositionType::tryFrom($rawPositionType);
        if ($positionType === null) {
            $positionType = self::FALLBACK_POSITION_TYPE;
            $positionTypeFallbacks++;
            Log::warning('Position import: invalid position_type, using fallback', [
                'import_position_id' => $importPositionId,
                'raw_position_type' => $rawPositionType,
                'fallback' => $positionType->value,
            ]);
        }

        $vacationPolicyId = $vacationPolicyIds->get($positionType->value);
        if ($vacationPolicyId === null) {
            Log::error('Position import: no vacation policy for position type (should not happen)', [
                'import_position_id' => $importPositionId,
                'position_type' => $positionType->value,
            ]);

            return 'missing_vacation_policy';
        }

        $status = $this->resolvePositionStatus($row->status ?? null);

        Position::query()->updateOrCreate(
            [
                'employee_id' => $employeeId,
                'department_id' => $departmentId,
            ],
            [
                'place_id' => $placeId,
                'vacation_policy_id' => $vacationPolicyId,
                'position_type' => $positionType,
                'date_start' => $this->normalizeDate($row->date_start ?? null),
                'date_end' => $this->normalizeDate($row->date_end ?? null),
                'status' => $status,
                'act_number' => $this->stringOrNull($row->act_number ?? null),
                'act_date' => $this->normalizeDate($row->act_date ?? null),
                'staff_type' => $this->stringOrNull($row->staff_type ?? null),
                'clinical' => $this->toBoolOrNull($row->clinical ?? null),
                'clinical_text' => $this->stringOrNull($row->clinical_text ?? null),
                'automative_renewal' => null,
                'salary' => isset($row->salary) && $row->salary !== '' ? (int) $row->salary : null,
                'comment' => null,
            ]
        );

        return true;
    }

    /**
     * @param  Collection<string, int>  $map  name => id
     */
    private function resolveDepartmentId(
        ?string $departmentName,
        Collection $map,
        int &$createdCount,
        int $importPositionId
    ): int {
        $name = $departmentName ?? self::PLACEHOLDER_DEPARTMENT_NAME;
        if ($name === '') {
            $name = self::PLACEHOLDER_DEPARTMENT_NAME;
        }

        if ($map->has($name)) {
            return (int) $map->get($name);
        }

        $department = Department::query()->firstOrCreate(
            ['name' => $name],
            [
                'slug' => $this->uniqueDepartmentSlug($name),
                'status' => DepartmentStatus::ACTIVE,
                'order' => 0,
            ]
        );

        if ($department->wasRecentlyCreated) {
            $createdCount++;
            Log::info('Position import: created department from import name', [
                'import_position_id' => $importPositionId,
                'department_id' => $department->id,
                'name' => $name,
            ]);
        }

        $map->put($name, $department->id);

        return (int) $department->id;
    }

    /**
     * @param  Collection<string, int>  $map  name => id
     */
    private function resolvePlaceId(
        ?string $placeName,
        Collection $map,
        int &$createdCount,
        int $importPositionId
    ): int {
        $name = $placeName ?? self::PLACEHOLDER_PLACE_NAME;
        if ($name === '') {
            $name = self::PLACEHOLDER_PLACE_NAME;
        }

        if ($map->has($name)) {
            return (int) $map->get($name);
        }

        $place = Place::query()->firstOrCreate(
            ['name' => $name],
            ['is_active' => true]
        );

        if ($place->wasRecentlyCreated) {
            $createdCount++;
            Log::info('Position import: created place from import name', [
                'import_position_id' => $importPositionId,
                'place_id' => $place->id,
                'name' => $name,
            ]);
        }

        $map->put($name, $place->id);

        return (int) $place->id;
    }

    /**
     * Non-Latin names may produce an empty {@see Str::slug()} result; DB requires a unique slug.
     */
    private function uniqueDepartmentSlug(string $name): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'import-dept-'.substr(md5($name), 0, 16);
        }

        $slug = $base;
        $suffix = 0;
        while (Department::query()->where('slug', $slug)->exists()) {
            $suffix++;
            $slug = $base.'-'.$suffix;
        }

        return $slug;
    }

    private function clearPositionsTable(): void
    {
        DB::table('positions')->delete();
        DB::statement('ALTER TABLE positions AUTO_INCREMENT = 1');
    }

    /**
     * Align lookup keys with {@see EmployeeImportService} normalization (11-digit padding for numeric PN).
     */
    private function normalizePersonalNumberForLookup(mixed $fromImport): ?string
    {
        $trimmed = $this->stringOrNull($fromImport);
        if ($trimmed === null) {
            return null;
        }

        if (strlen($trimmed) > self::PERSONAL_NUMBER_LENGTH) {
            return null;
        }

        if (ctype_digit($trimmed) && strlen($trimmed) < self::PERSONAL_NUMBER_LENGTH) {
            return str_pad($trimmed, self::PERSONAL_NUMBER_LENGTH, '0', STR_PAD_LEFT);
        }

        return $trimmed;
    }

    private function resolvePositionStatus(mixed $raw): ?PositionStatus
    {
        if ($raw === null || $raw === '' || $raw === '0' || $raw === 0) {
            return null;
        }

        return PositionStatus::tryFrom((string) $raw);
    }

    private function normalizeDate(mixed $value): ?string
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

    private function toBoolOrNull(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (bool) $value;
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
