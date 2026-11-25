<?php

use App\Enums\DepartmentStatus;
use App\Models\Department;
use App\Models\User;
use App\Support\PersonalDataReviewer;
use Database\Seeders\PersonalDataReviewerSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(LazilyRefreshDatabase::class);

test('the seeder creates one reviewer login per named person and can run twice', function () {
    foreach ([272, 78, 73, 75, 74, 76, 77] as $departmentId) {
        Department::query()->forceCreate([
            'id' => $departmentId,
            'name' => 'Faculty '.$departmentId,
            'slug' => 'faculty-'.$departmentId,
            'status' => DepartmentStatus::ACTIVE,
        ]);
    }

    $seeder = new PersonalDataReviewerSeeder;
    $seeder->run();

    $reviewers = User::role(PersonalDataReviewer::ROLE)->get();

    expect($reviewers)->toHaveCount(13)
        ->and($reviewers->every(fn (User $user): bool => $user->force_renew_password))->toBeTrue()
        ->and(User::query()->where('department_id', 73)->count())->toBe(2)
        ->and(User::query()->where('email', 'kebralidze@tsmu.edu')->value('department_id'))->toBe(272)
        ->and(User::query()->where('email', 'mtatanashvili@tsmu.edu')->value('name'))->toBe('მარიამ ტატანაშვილი')
        ->and(User::query()->where('email', 'mbibilashvili@tsmu.edu')->value('name'))->toBe('მაგდა ბიბილაშვილი')
        ->and(User::query()->where('email', 'mbibilashvili@tsmu.edu')->value('department_id'))->toBe(76)
        ->and(User::query()->where('email', 'ntezelashvili@tsmu.edu')->value('name'))->toBe('ნინო თეზელაშვილი')
        ->and(User::query()->where('email', 'ntezelashvili@tsmu.edu')->value('department_id'))->toBe(77);

    $magda = User::query()->where('email', 'mbibilashvili@tsmu.edu')->first();
    $magda->update([
        'password' => 'changed-password',
        'name' => 'Temporary name',
        'department_id' => 74,
    ]);

    $seeder->run();

    $magda->refresh();

    expect(User::role(PersonalDataReviewer::ROLE)->count())->toBe(13)
        ->and(Hash::check('changed-password', $magda->password))->toBeTrue()
        ->and($magda->name)->toBe('მაგდა ბიბილაშვილი')
        ->and($magda->department_id)->toBe(76)
        ->and($magda->hasRole(PersonalDataReviewer::ROLE))->toBeTrue();
});

test('the seeder fails when a faculty department is missing', function () {
    new PersonalDataReviewerSeeder()->run();
})->throws(RuntimeException::class, 'Missing departments:');
