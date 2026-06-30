<?php

use App\Enums\DepartmentStatus;
use App\Enums\PositionStatus;
use App\Enums\PositionType;
use App\Enums\PublicHolidayKind;
use App\Enums\StatusEnum;
use App\Enums\VacationStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Place;
use App\Models\Position;
use App\Models\PublicHoliday;
use App\Models\Vacation;
use App\Models\VacationPolicy;
use App\Services\VacationWorkingDaysCalculator;
use Carbon\Carbon;
use Illuminate\Support\Str;

test('working days exclude public holidays in range', function () {
    $policy = VacationPolicy::query()->create([
        'position_type' => PositionType::AcademicPersonnel->value,
        'name' => 'Test policy',
        'description' => 'Test',
        'status' => StatusEnum::ACTIVE->value,
        'settings' => [
            ['key' => 'days', 'value' => '24'],
            ['key' => 'saturday_allowed', 'value' => false],
            ['key' => 'sunday_allowed', 'value' => false],
        ],
    ]);

    $position = Position::query()->create([
        'place_id' => Place::query()->create([
            'name' => 'Test place',
            'is_active' => true,
        ])->id,
        'employee_id' => Employee::factory()->create()->id,
        'department_id' => Department::query()->create([
            'name' => 'Test department',
            'slug' => Str::slug('test-dept-'.uniqid()),
            'status' => DepartmentStatus::ACTIVE,
        ])->id,
        'vacation_policy_id' => $policy->id,
        'position_type' => PositionType::AcademicPersonnel,
        'date_start' => '2020-01-01',
        'date_end' => '2030-12-31',
        'status' => PositionStatus::Appointment,
        'act_number' => '1',
        'act_date' => '2020-01-01',
        'staff_type' => '1',
        'clinical' => false,
        'automative_renewal' => false,
        'salary' => 1000,
    ]);

    PublicHoliday::query()->create([
        'date' => '2026-04-08',
        'kind' => PublicHolidayKind::Regular,
        'series_id' => null,
        'name' => 'Mid-week',
    ]);

    $start = Carbon::parse('2026-04-06');
    $end = Carbon::parse('2026-04-10');

    $calculator = app(VacationWorkingDaysCalculator::class);

    expect($calculator->countWorkingDaysInRange($start, $end, $position))->toBe(4)
        ->and($calculator->countPublicHolidaysExcludedInRange($start, $end, $position))->toBe(1);
});

test('recalculate reduces vacation working days when public holiday overlaps', function () {
    $policy = VacationPolicy::query()->create([
        'position_type' => PositionType::AdministrativePersonnel->value,
        'name' => 'Test policy 2',
        'description' => 'Test',
        'status' => StatusEnum::ACTIVE->value,
        'settings' => [
            ['key' => 'days', 'value' => '24'],
            ['key' => 'saturday_allowed', 'value' => false],
            ['key' => 'sunday_allowed', 'value' => false],
        ],
    ]);

    $employee = Employee::factory()->create();

    $position = Position::query()->create([
        'place_id' => Place::query()->create([
            'name' => 'Test place',
            'is_active' => true,
        ])->id,
        'employee_id' => $employee->id,
        'department_id' => Department::query()->create([
            'name' => 'Test department',
            'slug' => Str::slug('test-dept-'.uniqid()),
            'status' => DepartmentStatus::ACTIVE,
        ])->id,
        'vacation_policy_id' => $policy->id,
        'position_type' => PositionType::AdministrativePersonnel,
        'date_start' => '2020-01-01',
        'date_end' => '2030-12-31',
        'status' => PositionStatus::Appointment,
        'act_number' => '1',
        'act_date' => '2020-01-01',
        'staff_type' => '1',
        'clinical' => false,
        'automative_renewal' => false,
        'salary' => 1000,
    ]);

    $vacation = Vacation::query()->create([
        'employee_id' => $employee->id,
        'position_id' => $position->id,
        'start_date' => '2026-04-06',
        'end_date' => '2026-04-10',
        'working_days_count' => 5,
        'status' => VacationStatus::Approved,
        'reason' => null,
        'notes' => null,
    ]);

    PublicHoliday::withoutEvents(function (): void {
        PublicHoliday::query()->create([
            'date' => '2026-04-08',
            'kind' => PublicHolidayKind::Regular,
            'series_id' => null,
            'name' => null,
        ]);
    });

    app(VacationWorkingDaysCalculator::class)->recalculateVacationsOverlappingDateRange(
        Carbon::parse('2026-04-08'),
        Carbon::parse('2026-04-08'),
    );

    expect($vacation->fresh()->working_days_count)->toBe(4);
});
