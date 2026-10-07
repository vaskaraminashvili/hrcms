<?php

use App\Enums\AcademicPosition as AcademicPositionTitle;
use App\Enums\EmployeeStatusEnum;
use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\Schemas\EmployeeForm;
use App\Models\AcademicDegree;
use App\Models\AcademicPosition;
use App\Models\Employee;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('personal file repeaters skip a blank row and remove a cleared one', function () {
    $employee = Employee::query()->create([
        'name' => 'Test',
        'surname' => 'Employee',
        'personal_number' => '12345678901',
        'birth_date' => '1990-01-01',
        'status' => EmployeeStatusEnum::ACTIVE,
    ]);

    $repeaters = collect(
        EmployeeForm::configure(Schema::make(new EditEmployee)->model($employee))
            ->getFlatComponents(withHidden: true),
    )->filter(fn (mixed $component): bool => $component instanceof Repeater)->keyBy(fn (Repeater $repeater): string => $repeater->getName());

    $academicPositions = $repeaters->get('academicPositions');
    $academicDegrees = $repeaters->get('academicDegrees');
    $educations = $repeaters->get('educations');

    expect($academicPositions)->toBeInstanceOf(Repeater::class)
        ->and($academicDegrees)->toBeInstanceOf(Repeater::class)
        ->and($educations)->toBeInstanceOf(Repeater::class)
        ->and($academicPositions->mutateRelationshipDataBeforeCreate([
            'title' => null,
            'sort' => 1,
        ]))->toBeNull()
        ->and($academicDegrees->mutateRelationshipDataBeforeCreate([
            'degree' => null,
            'other' => ['ka' => '', 'en' => ''],
            'sort' => 1,
        ]))->toBeNull()
        ->and($educations->mutateRelationshipDataBeforeCreate([
            'institution' => ['ka' => '', 'en' => ''],
            'program' => ['ka' => null, 'en' => null],
            'specialty' => ['ka' => '', 'en' => ''],
            'started_at' => null,
            'ended_at' => null,
            'sort' => 1,
        ]))->toBeNull()
        ->and($academicPositions->mutateRelationshipDataBeforeCreate([
            'title' => AcademicPositionTitle::ASSISTANT->value,
            'sort' => 1,
        ]))->toBe([
            'title' => AcademicPositionTitle::ASSISTANT->value,
            'sort' => 1,
        ]);

    $position = $employee->academicPositions()->create([
        'title' => AcademicPositionTitle::PROFESSOR->value,
        'sort' => 1,
    ]);
    $degree = $employee->academicDegrees()->create([
        'degree' => 'DOCTOR',
        'sort' => 1,
    ]);

    expect($academicPositions->mutateRelationshipDataBeforeSave([
        'title' => null,
        'sort' => 1,
    ], $position))->toBeNull()
        ->and($academicDegrees->mutateRelationshipDataBeforeSave([
            'degree' => null,
            'other' => ['ka' => '', 'en' => ''],
            'sort' => 1,
        ], $degree))->toBeNull();

    expect(AcademicPosition::query()->whereKey($position->id)->exists())->toBeFalse()
        ->and(AcademicPosition::withTrashed()->whereKey($position->id)->exists())->toBeTrue()
        ->and(AcademicDegree::query()->whereKey($degree->id)->exists())->toBeFalse()
        ->and(AcademicDegree::withTrashed()->whereKey($degree->id)->exists())->toBeTrue();
});
