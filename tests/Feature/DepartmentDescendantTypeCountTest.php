<?php

use App\Enums\DepartmentStatus;
use App\Enums\DepartmentType;
use App\Models\Department;
use App\Models\User;
use App\Services\DepartmentDescendantTypeCountService;
use Illuminate\Support\Str;

test('descendant type counts payload aggregates child types', function (): void {
    $root = Department::query()->create([
        'name' => 'Root',
        'slug' => Str::slug('root-'.uniqid()),
        'status' => DepartmentStatus::ACTIVE,
        'parent_id' => null,
    ]);

    Department::query()->create([
        'name' => 'Child A',
        'slug' => Str::slug('child-a-'.uniqid()),
        'status' => DepartmentStatus::ACTIVE,
        'parent_id' => $root->id,
        'type' => DepartmentType::CENTER,
    ]);

    Department::query()->create([
        'name' => 'Child B',
        'slug' => Str::slug('child-b-'.uniqid()),
        'status' => DepartmentStatus::ACTIVE,
        'parent_id' => $root->id,
        'type' => DepartmentType::MUSEUM,
    ]);

    $service = app(DepartmentDescendantTypeCountService::class);
    $payload = $service->descendantTypeCountsPayload($root);

    expect($payload)->toHaveCount(2);

    $byLabel = collect($payload)->keyBy('label');

    expect($byLabel[DepartmentType::CENTER->getLabel()]['count'])->toBe(1);
    expect($byLabel[DepartmentType::MUSEUM->getLabel()]['count'])->toBe(1);
});

test('department type counts endpoint returns json payload', function (): void {
    $user = User::factory()->create();

    $root = Department::query()->create([
        'name' => 'Root',
        'slug' => Str::slug('root-api-'.uniqid()),
        'status' => DepartmentStatus::ACTIVE,
        'parent_id' => null,
    ]);

    Department::query()->create([
        'name' => 'Child',
        'slug' => Str::slug('child-api-'.uniqid()),
        'status' => DepartmentStatus::ACTIVE,
        'parent_id' => $root->id,
        'type' => DepartmentType::DEPARTMENT,
    ]);

    $service = app(DepartmentDescendantTypeCountService::class);
    $expected = $service->getCachedDescendantTypeCountsPayload($root);

    $response = $this->actingAs($user)->getJson(route('departments.type-counts', $root));

    $response->assertOk()->assertExactJson($expected);
});
