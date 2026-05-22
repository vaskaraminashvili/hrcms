<?php

use App\Enums\DepartmentStatus;
use App\Models\Department;
use App\Services\DepartmentSiblingOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('shiftSiblingsOpeningSlot increments every sibling at or above the desired order', function (): void {
    $svc = app(DepartmentSiblingOrderService::class);

    $one = Department::query()->create([
        'name' => 'Alpha',
        'slug' => 'alpha-sh-'.Str::uuid(),
        'status' => DepartmentStatus::ACTIVE,
        'parent_id' => null,
        'order' => 2,
    ]);

    $two = Department::query()->create([
        'name' => 'Beta',
        'slug' => 'beta-sh-'.Str::uuid(),
        'status' => DepartmentStatus::ACTIVE,
        'parent_id' => null,
        'order' => 5,
    ]);

    $svc->shiftSiblingsOpeningSlot(null, 2, excludeDepartmentId: null);

    expect($one->fresh()->order)->toBe(3)
        ->and($two->fresh()->order)->toBe(6);
});

test('shiftSiblingsOpeningSlot skips the excluded department row', function (): void {
    $svc = app(DepartmentSiblingOrderService::class);

    $moved = Department::query()->create([
        'name' => 'Moved',
        'slug' => 'moved-sh-'.Str::uuid(),
        'status' => DepartmentStatus::ACTIVE,
        'parent_id' => null,
        'order' => 10,
    ]);

    $other = Department::query()->create([
        'name' => 'Other',
        'slug' => 'other-sh-'.Str::uuid(),
        'status' => DepartmentStatus::ACTIVE,
        'parent_id' => null,
        'order' => 2,
    ]);

    $svc->shiftSiblingsOpeningSlot(null, 2, excludeDepartmentId: $moved->getKey());

    expect((int) $moved->fresh()->order)->toBe(10)
        ->and((int) $other->fresh()->order)->toBe(3);

    $moved->forceFill(['order' => 2])->saveQuietly();

    expect((int) $moved->fresh()->order)->toBe(2)
        ->and((int) $other->fresh()->order)->toBe(3);
});

test('shouldApplyShiftForSave is false when parent and order are unchanged', function (): void {
    $svc = app(DepartmentSiblingOrderService::class);

    expect($svc->shouldApplyShiftForSave(null, 3, null, 3, false))->toBeFalse();
});

test('shouldApplyShiftForSave is true when order or parent changed', function (): void {
    $svc = app(DepartmentSiblingOrderService::class);

    expect($svc->shouldApplyShiftForSave(null, 1, null, 2, false))->toBeTrue()
        ->and($svc->shouldApplyShiftForSave(1, 0, null, 0, false))->toBeTrue();
});
