<?php

use App\Filament\Resources\Employees\Schemas\PersonalFile\PersonalFileItemPayload;

test('a new personal file row with no entered values is not stored', function (array $data) {
    expect(PersonalFileItemPayload::withoutBlankItem($data, creating: true))->toBeNull();
})->with([
    'empty item' => [[]],
    'blank academic position' => [['title' => null, 'sort' => 1]],
    'blank academic degree' => [['degree' => '', 'other' => ['ka' => '', 'en' => null], 'sort' => 1]],
    'blank translated fields' => [[
        'institution' => ['ka' => '', 'en' => ''],
        'program' => ['ka' => null, 'en' => null],
        'started_at' => null,
        'sort' => 2,
    ]],
]);

test('a new personal file row with an entered value is stored', function () {
    $data = [
        'degree' => 'DOCTOR',
        'other' => ['ka' => '', 'en' => ''],
        'sort' => 1,
    ];

    expect(PersonalFileItemPayload::withoutBlankItem($data, creating: true))->toBe($data);
});

test('clearing every field on an existing personal file row removes that row', function () {
    expect(PersonalFileItemPayload::withoutBlankItem([
        'degree' => null,
        'other' => ['ka' => '', 'en' => ''],
        'sort' => 1,
    ]))->toBeNull();
});

test('an existing personal file row is left unchanged when its fields are not in the form data', function () {
    $data = ['sort' => 2];

    expect(PersonalFileItemPayload::withoutBlankItem($data))->toBe($data);
});
