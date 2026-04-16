<?php

namespace App\Services;

use App\Enums\DepartmentStatus;
use App\Enums\PositionHistoryAffectField;
use App\Enums\PositionHistorySnapshotField;
use App\Enums\PositionStatus;
use App\Enums\PositionType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Place;
use App\Models\Position;
use App\Models\PositionHistory;
use App\Models\VacationPolicy;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use stdClass;

/**
 * One-off import from legacy `import_*` tables into `positions`.
 * Maps local employees (personal_number); creates missing departments/places by name when needed.
 * Multiple legacy rows for the same employee+department+place collapse to one {@see Position}: the row with
 * the latest `date_start` wins; other rows are stored as {@see PositionHistory} (`import_legacy_period`).
 */
class PositionImportService
{
    private const PERSONAL_NUMBER_LENGTH = 11;

    private const PLACEHOLDER_DEPARTMENT_NAME = '[Import] Unknown department';

    private const PLACEHOLDER_PLACE_NAME = '[Import] Unknown place';

    private const FALLBACK_POSITION_TYPE = PositionType::AdministrativePersonnel;

    /**
     * History rows for prior import periods when multiple legacy rows share employee+department+place.
     */
    private const IMPORT_LEGACY_HISTORY_EVENT = 'import_legacy_period';

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

        $rows = DB::table('import_positions')
            ->join('import_employees', 'import_positions.employee_id', '=', 'import_employees.id')
            ->join('import_departments', 'import_positions.department_id', '=', 'import_departments.id')
            ->join('import_places', 'import_positions.place_id', '=', 'import_places.id')
            ->select([
                'import_positions.id',
                'import_positions.employee_id as import_employee_ref',
                'import_positions.department_id as import_department_ref',
                'import_positions.place_id as import_place_ref',
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
            ->orderBy('import_positions.department_id')
            ->orderBy('import_positions.place_id')
            ->orderBy('import_positions.date_start')
            ->orderBy('import_positions.id')
            ->get();

        $grouped = $rows->groupBy(
            fn (stdClass $r): string => (string) (int) $r->import_employee_ref
                .'|'.(string) (int) $r->import_department_ref
                .'|'.(string) (int) $r->import_place_ref
        );

        foreach ($grouped as $groupRows) {
            DB::transaction(function () use (
                $groupRows,
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
                $groupSize = $groupRows->count();
                $result = $this->importImportPositionGroup(
                    $groupRows,
                    $places,
                    $departments,
                    $employees,
                    $vacationPolicyIds,
                    $departmentsCreated,
                    $placesCreated,
                    $positionTypeFallbacks
                );
                if ($result === true) {
                    $imported += $groupSize;
                } elseif (is_string($result)) {
                    $skipped += $groupSize;
                    $skipReasons[$result] = ($skipReasons[$result] ?? 0) + $groupSize;
                }
            });
        }

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
     * @param  Collection<int, stdClass>  $groupRows
     * @param  Collection<string, int>  $places
     * @param  Collection<string, int>  $departments
     * @param  Collection<string, int>  $employees
     */
    private function importImportPositionGroup(
        Collection $groupRows,
        Collection $places,
        Collection $departments,
        Collection $employees,
        Collection $vacationPolicyIds,
        int &$departmentsCreated,
        int &$placesCreated,
        int &$positionTypeFallbacks
    ): bool|string {
        /** @var list<array<string, mixed>> $resolved */
        $resolved = [];
        foreach ($groupRows as $row) {
            $one = $this->resolveImportPositionRow(
                $row,
                $places,
                $departments,
                $employees,
                $vacationPolicyIds,
                $departmentsCreated,
                $placesCreated,
                $positionTypeFallbacks
            );
            if (is_string($one)) {
                return $one;
            }
            $resolved[] = $one;
        }

        $collection = collect($resolved);
        $winner = $this->selectWinnerResolvedRow($collection);

        $position = Position::query()->updateOrCreate(
            [
                'employee_id' => $winner['employee_id'],
                'department_id' => $winner['department_id'],
                'place_id' => $winner['place_id'],
            ],
            $winner['attributes']
        );

        if ($collection->count() < 2) {
            return true;
        }

        $losers = $collection->filter(
            fn (array $row): bool => $row['import_position_id'] !== $winner['import_position_id']
        )->sortBy([
            fn (array $r): float => $this->dateStartSortValue($r['date_start']),
            fn (array $r): int => $r['import_position_id'],
        ]);

        foreach ($losers as $loser) {
            $this->storeImportLegacyHistory($position, $loser);
        }

        return true;
    }

    /**
     * Row with the latest {@see date_start} wins the canonical {@see Position}; ties break on {@see import_position_id}.
     *
     * @param  Collection<int, array<string, mixed>>  $resolvedRows
     * @return array<string, mixed>
     */
    private function selectWinnerResolvedRow(Collection $resolvedRows): array
    {
        return $resolvedRows->sortBy([
            fn (array $r): float => $this->dateStartSortValue($r['date_start']),
            fn (array $r): int => $r['import_position_id'],
        ])->last();
    }

    private function dateStartSortValue(?string $normalizedDateStart): float
    {
        if ($normalizedDateStart === null) {
            return -INF;
        }

        return (float) strtotime($normalizedDateStart);
    }

    /**
     * @param  array<string, mixed>  $resolved
     */
    private function storeImportLegacyHistory(Position $canonical, array $resolved): void
    {
        PositionHistory::query()->create([
            'position_id' => $canonical->id,
            'changed_by' => null,
            'event_type' => self::IMPORT_LEGACY_HISTORY_EVENT,
            'snapshot' => $this->buildHistorySnapshotForResolvedRow($canonical, $resolved),
            'changed_fields' => null,
            ...collect(PositionHistoryAffectField::cases())
                ->mapWithKeys(fn (PositionHistoryAffectField $field) => [
                    $field->value => false,
                ])
                ->all(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $resolved
     * @return array<string, mixed>
     */
    private function buildHistorySnapshotForResolvedRow(Position $canonical, array $resolved): array
    {
        $snapshotModel = new Position;
        $snapshotModel->forceFill(array_merge($resolved['attributes'], [
            'employee_id' => $resolved['employee_id'],
            'department_id' => $resolved['department_id'],
            'place_id' => $resolved['place_id'],
        ]));
        $snapshotModel->id = $canonical->id;

        return Arr::except($snapshotModel->toArray(), PositionHistorySnapshotField::EXCLUDED_FROM_HISTORY);
    }

    /**
     * @param  Collection<string, int>  $places  name => id (mutated when new places are created)
     * @param  Collection<string, int>  $departments  name => id (mutated when new departments are created)
     * @param  Collection<string, int>  $employees  personal_number => id
     * @param  Collection<string, int>  $vacationPolicyIds  position_type value => id
     * @return array<string, mixed>|string
     */
    private function resolveImportPositionRow(
        stdClass $row,
        Collection $places,
        Collection $departments,
        Collection $employees,
        Collection $vacationPolicyIds,
        int &$departmentsCreated,
        int &$placesCreated,
        int &$positionTypeFallbacks
    ): array|string {
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

        $dateStart = $this->normalizeDate($row->date_start ?? null);

        return [
            'import_position_id' => $importPositionId,
            'employee_id' => $employeeId,
            'department_id' => $departmentId,
            'place_id' => $placeId,
            'date_start' => $dateStart,
            'attributes' => [
                'vacation_policy_id' => $vacationPolicyId,
                'position_type' => $positionType,
                'date_start' => $dateStart,
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
            ],
        ];
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
