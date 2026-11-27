<?php

use App\Enums\EmployeeStatusEnum;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('the cv page can open the same layout for download', function () {
    $user = User::factory()->create();
    $employee = Employee::query()->create([
        'name' => 'ნინო',
        'surname' => 'თეზელაშვილი',
        'personal_number' => '12345678901',
        'birth_date' => '1990-01-01',
        'citizenship' => 'ქართველი',
        'status' => EmployeeStatusEnum::ACTIVE,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('employees.cv.show', ['employee' => $employee, 'locale' => 'ka', 'download' => 1]))
        ->assertOk()
        ->assertSee('window.print()', false)
        ->assertSee(__('cv.download'));
});
