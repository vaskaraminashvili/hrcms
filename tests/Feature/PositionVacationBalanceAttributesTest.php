<?php

use App\Enums\DepartmentStatus;
use App\Enums\PositionStatus;
use App\Enums\PositionType;
use App\Enums\StatusEnum;
use App\Enums\VacationStatus;
use App\Enums\VacationType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Place;
use App\Models\Position;
use App\Models\Vacation;
use App\Models\VacationPolicy;
use App\Models\VacationTransfer;
use Carbon\Carbon;
use Illuminate\Support\Str;

afterEach(fn () => Carbon::setTestNow());

test('position vacation balance accessors use calendar years, transfers, and paid-leave statuses', function () {
    Carbon::setTestNow('2026-06-15');

    $policy = VacationPolicy::query()->create([
        'position_type' => PositionType::AcademicPersonnel->value,
        'name' => 'Balance test policy',
        'description' => 'Test',
        'status' => StatusEnum::ACTIVE->value,
        'settings' => [
            ['key' => 'days', 'value' => '24'],
        ],
    ]);

    $employee = Employee::factory()->create();

    $position = Position::query()->create([
        'place_id' => Place::query()->create([
            'name' => 'Balance place',
            'is_active' => true,
        ])->id,
        'employee_id' => $employee->id,
        'department_id' => Department::query()->create([
            'name' => 'Balance dept',
            'slug' => Str::slug('balance-dept-'.uniqid()),
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

    VacationTransfer::query()->create([
        'position_id' => $position->id,
        'from_year' => 2025,
        'to_year' => 2026,
        'days_count' => 5,
    ]);

    Vacation::query()->create([
        'employee_id' => $employee->id,
        'position_id' => $position->id,
        'start_date' => '2025-07-02',
        'end_date' => '2025-07-05',
        'working_days_count' => 10,
        'status' => VacationStatus::Approved,
        'reason' => null,
        'notes' => null,
        'type' => VacationType::PAID_LEAVE,
    ]);

    Vacation::query()->create([
        'employee_id' => $employee->id,
        'position_id' => $position->id,
        'start_date' => '2026-02-03',
        'end_date' => '2026-02-07',
        'working_days_count' => 4,
        'status' => VacationStatus::Approved,
        'reason' => null,
        'notes' => null,
        'type' => VacationType::PAID_LEAVE,
    ]);

    Vacation::query()->create([
        'employee_id' => $employee->id,
        'position_id' => $position->id,
        'start_date' => '2026-03-10',
        'end_date' => '2026-03-14',
        'working_days_count' => 99,
        'status' => VacationStatus::Rejected,
        'reason' => null,
        'notes' => null,
        'type' => VacationType::PAID_LEAVE,
    ]);

    Vacation::query()->create([
        'employee_id' => $employee->id,
        'position_id' => $position->id,
        'start_date' => '2026-09-09',
        'end_date' => '2026-09-09',
        'working_days_count' => 1,
        'status' => VacationStatus::Approved,
        'reason' => null,
        'notes' => null,
        'type' => VacationType::DAY_OFF,
    ]);

    $position->loadMissing('vacationPolicy');

    expect($position->policy_days)->toBe(24)
        ->and($position->transferred_days)->toBe(5)
        ->and($position->total_vacation_days)->toBe(29)
        ->and($position->used_vacation_days_last_year)->toBe(10)
        ->and($position->vacation_days_left_this_year)->toBe(24)
        ->and($position->vacation_days_left_last_year)->toBe(9)
        ->and($position->vacation_days_left_this_year_plus_last_year)->toBe(34)
        ->and($position->available_vacation_days)->toBe(25)
        ->and($position->used_days_off_days)->toBe(1);
});

test('this-year policy grant balance ignores carryover until carryover is exhausted', function () {
    Carbon::setTestNow('2026-06-15');

    $policy = VacationPolicy::query()->create([
        'position_type' => PositionType::AcademicPersonnel->value,
        'name' => 'FIFO policy grant test',
        'description' => 'Test',
        'status' => StatusEnum::ACTIVE->value,
        'settings' => [
            ['key' => 'days', 'value' => '24'],
        ],
    ]);

    $employee = Employee::factory()->create();

    $position = Position::query()->create([
        'place_id' => Place::query()->create([
            'name' => 'FIFO place',
            'is_active' => true,
        ])->id,
        'employee_id' => $employee->id,
        'department_id' => Department::query()->create([
            'name' => 'FIFO dept',
            'slug' => Str::slug('fifo-dept-'.uniqid()),
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

    VacationTransfer::query()->create([
        'position_id' => $position->id,
        'from_year' => 2025,
        'to_year' => 2026,
        'days_count' => 8,
    ]);

    Vacation::query()->create([
        'employee_id' => $employee->id,
        'position_id' => $position->id,
        'start_date' => '2026-02-03',
        'end_date' => '2026-02-07',
        'working_days_count' => 3,
        'status' => VacationStatus::Approved,
        'reason' => null,
        'notes' => null,
        'type' => VacationType::PAID_LEAVE,
    ]);

    $position->loadMissing('vacationPolicy');

    expect($position->vacation_days_left_this_year)->toBe(24)
        ->and($position->available_vacation_days)->toBe(29);
});
