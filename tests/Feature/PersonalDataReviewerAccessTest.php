<?php

use App\Enums\DepartmentStatus;
use App\Enums\EmployeeStatusEnum;
use App\Enums\PositionStatus;
use App\Enums\PositionType;
use App\Enums\StatusEnum;
use App\Filament\Review\Auth\ReviewLogin;
use App\Filament\Review\Auth\ReviewRenewPassword;
use App\Filament\Review\Resources\Employees\EmployeeResource;
use App\Filament\Review\Resources\Employees\Pages\ListEmployees;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Place;
use App\Models\Position;
use App\Models\User;
use App\Models\VacationPolicy;
use App\Support\PersonalDataReviewer;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Filament::setCurrentPanel(Filament::getPanel('review'));
});

test('a faculty reviewer sees only academic and invited staff under that faculty', function () {
    $reviewer = reviewerUser();
    $place = Place::query()->create(['name' => 'Reviewer place '.Str::random(6), 'is_active' => true]);
    $policy = VacationPolicy::query()->create([
        'position_type' => PositionType::AcademicPersonnel->value,
        'name' => 'Reviewer policy',
        'status' => StatusEnum::ACTIVE->value,
        'settings' => [],
    ]);

    $faculty = $reviewer->department;
    $child = department('Child', $faculty->id);
    $deeper = department('Deeper', $child->id);
    $otherFaculty = department('Other faculty');
    $otherChild = department('Other child', $otherFaculty->id);

    $academic = reviewEmployee('Academic');
    $deeperAcademic = reviewEmployee('Deeper');
    $administrative = reviewEmployee('Administrative');
    $otherFacultyEmployee = reviewEmployee('Other');

    reviewPosition($academic, $child, $place, $policy, PositionType::AcademicPersonnel);
    reviewPosition($deeperAcademic, $deeper, $place, $policy, PositionType::InvitedTeacher);
    reviewPosition($administrative, $child, $place, $policy, PositionType::AdministrativePersonnel);
    reviewPosition($otherFacultyEmployee, $otherChild, $place, $policy, PositionType::AcademicPersonnel);

    $this->actingAs($reviewer);

    Livewire::test(ListEmployees::class)
        ->assertCanSeeTableRecords([$academic, $deeperAcademic])
        ->assertCanNotSeeTableRecords([$administrative, $otherFacultyEmployee]);

    expect($reviewer->can('update', $academic))->toBeTrue()
        ->and($reviewer->can('update', $otherFacultyEmployee))->toBeFalse()
        ->and($reviewer->canAccessPanel(Filament::getPanel('admin')))->toBeFalse()
        ->and($reviewer->canAccessPanel(Filament::getPanel('review')))->toBeTrue()
        ->and(EmployeeResource::getRelations())->toBe([]);

    $this->get(EmployeeResource::getUrl('edit', ['record' => $academic]))
        ->assertSuccessful();

    $this->get(EmployeeResource::getUrl('edit', ['record' => $otherFacultyEmployee]))
        ->assertNotFound();

    $this->get('/cms')->assertForbidden();

    $this->get('/review')->assertRedirect(EmployeeResource::getUrl('index'));
});

test('replacing the temporary password continues to the employee list', function () {
    $reviewer = reviewerUser();
    $reviewer->forceFill([
        'password' => 'password',
        'force_renew_password' => true,
    ])->save();

    $employeesUrl = EmployeeResource::getUrl('index');

    Livewire::test(ReviewLogin::class)
        ->fillForm([
            'email' => $reviewer->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertRedirect($employeesUrl);

    $this->actingAs($reviewer)
        ->get($employeesUrl)
        ->assertRedirect(route('filament.review.auth.password.renew'));

    Livewire::test(ReviewRenewPassword::class)
        ->fillForm([
            'currentPassword' => 'password',
            'password' => 'new-password-1',
            'passwordConfirmation' => 'new-password-1',
        ])
        ->call('renew')
        ->assertRedirect($employeesUrl);
});

function reviewerUser(): User
{
    $faculty = department('Faculty');

    $user = User::factory()->create([
        'department_id' => $faculty->id,
        'force_renew_password' => false,
    ]);

    $user->assignRole(Role::findOrCreate(PersonalDataReviewer::ROLE, 'web'));

    return $user;
}

function department(string $name, ?int $parentId = null): Department
{
    return Department::query()->create([
        'name' => $name.' '.Str::random(6),
        'slug' => Str::slug($name.'-'.Str::uuid()),
        'status' => DepartmentStatus::ACTIVE,
        'parent_id' => $parentId,
    ]);
}

function reviewEmployee(string $name): Employee
{
    return Employee::query()->create([
        'name' => $name,
        'surname' => 'Review',
        'personal_number' => str_pad((string) random_int(1, 99999999999), 11, '0', STR_PAD_LEFT),
        'birth_date' => '1990-01-01',
        'status' => EmployeeStatusEnum::ACTIVE,
    ]);
}

function reviewPosition(
    Employee $employee,
    Department $department,
    Place $place,
    VacationPolicy $policy,
    PositionType $type,
): Position {
    return Position::query()->create([
        'employee_id' => $employee->id,
        'department_id' => $department->id,
        'place_id' => $place->id,
        'vacation_policy_id' => $policy->id,
        'position_type' => $type,
        'date_start' => '2024-01-01',
        'date_end' => '2027-12-31',
        'status' => PositionStatus::Appointment,
    ]);
}
