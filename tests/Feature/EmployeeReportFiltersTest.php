<?php

use App\Enums\DepartmentStatus;
use App\Enums\PositionStatus;
use App\Enums\PositionType;
use App\Enums\StatusEnum;
use App\Filament\Pages\EmployeeReport;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Place;
use App\Models\Position;
use App\Models\User;
use App\Models\VacationPolicy;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Carbon\Carbon::setTestNow('2026-10-28');
    $this->withoutVite();

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('super_admin', 'web'));
    $this->actingAs($user);
});

afterEach(fn () => Carbon\Carbon::setTestNow());

test('employee report filters created at with a date range', function () {
    $included = reportEmployee('2024-06-15 12:00:00');
    $excluded = reportEmployee('2019-01-01 12:00:00');

    Livewire::test(EmployeeReport::class)
        ->filterTable('created_at', [
            'created_from' => '2024-01-01',
            'created_until' => '2024-12-31',
        ])
        ->assertCanSeeTableRecords([$included])
        ->assertCanNotSeeTableRecords([$excluded]);
});

test('position filters apply to the same appointment', function () {
    $policy = VacationPolicy::query()->create([
        'position_type' => PositionType::AcademicPersonnel->value,
        'name' => 'Report filter policy',
        'status' => StatusEnum::ACTIVE->value,
        'settings' => [],
    ]);

    $dean = Place::query()->create(['name' => 'Dean report place', 'is_active' => true]);
    $clerk = Place::query()->create(['name' => 'Clerk report place', 'is_active' => true]);

    $academic = reportEmployee();
    $admin = reportEmployee();
    $split = reportEmployee();

    reportPosition($academic, $dean, PositionType::AcademicPersonnel, '2024-02-01', '2027-12-31', $policy->id);
    reportPosition($admin, $clerk, PositionType::AdministrativePersonnel, '2018-01-01', '2027-06-01', $policy->id);
    reportPosition($split, $dean, PositionType::AdministrativePersonnel, '2024-02-01', '2027-12-31', $policy->id);
    reportPosition($split, $clerk, PositionType::AcademicPersonnel, '2018-01-01', '2027-12-31', $policy->id);

    Livewire::test(EmployeeReport::class)
        ->assertSee('01.02.2024')
        ->filterTable('appointment_position', [
            'place_id' => $dean->id,
            'position_type' => [PositionType::AcademicPersonnel->value],
            'date_start' => '2024-01-01',
            'date_end' => '2027-12-31',
            'status' => PositionStatus::Appointment->value,
        ])
        ->assertCanSeeTableRecords([$academic])
        ->assertCanNotSeeTableRecords([$admin, $split]);

    Livewire::test(EmployeeReport::class)
        ->filterTable('appointment_position', [
            'position_type' => [
                PositionType::AcademicPersonnel->value,
                PositionType::AdministrativePersonnel->value,
            ],
        ])
        ->assertCanSeeTableRecords([$academic, $admin, $split]);
});

function reportEmployee(?string $createdAt = null): Employee
{
    $employee = Employee::query()->create([
        'name' => 'Report',
        'surname' => 'Employee',
        'personal_number' => str_pad((string) random_int(1, 99999999999), 11, '0', STR_PAD_LEFT),
        'birth_date' => '1990-01-01',
    ]);

    if ($createdAt !== null) {
        $employee->forceFill(['created_at' => $createdAt])->save();
    }

    return $employee;
}

function reportPosition(
    Employee $employee,
    Place $place,
    PositionType $type,
    string $dateStart,
    string $dateEnd,
    int $policyId,
): Position {
    return Position::query()->create([
        'employee_id' => $employee->id,
        'department_id' => Department::query()->create([
            'name' => 'Report dept '.$employee->id.' '.$place->id,
            'slug' => 'report-dept-'.$employee->id.'-'.$place->id,
            'status' => DepartmentStatus::ACTIVE,
        ])->id,
        'place_id' => $place->id,
        'vacation_policy_id' => $policyId,
        'position_type' => $type,
        'date_start' => $dateStart,
        'date_end' => $dateEnd,
        'status' => PositionStatus::Appointment,
    ]);
}
