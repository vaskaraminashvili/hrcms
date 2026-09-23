<?php

use App\Enums\PublicationScope;
use App\Enums\ScientificForumRole;
use App\Imports\ScientificForumsImport;
use App\Models\Employee;
use App\Models\ScientificForum;
use App\Services\EmployeeCvService;

test('scientific forums import defaults missing role and scope columns', function () {
    $import = new ScientificForumsImport(1);

    $forum = $import->model([
        'title_ka' => 'კონფერენცია',
        'title_en' => 'Conference',
    ]);

    expect($forum)->not->toBeNull()
        ->and($forum->participation_role)->toBeNull()
        ->and($forum->scope)->toBe(PublicationScope::Local->value);
});

test('scientific forums import maps role and scope from excel columns', function () {
    $import = new ScientificForumsImport(1);

    $forum = $import->model([
        'title_ka' => 'კონფერენცია',
        'title_en' => 'Conference',
        'participation_role' => 'მომხსენებელი',
        'scope' => 'საერთაშორისო',
    ]);

    expect($forum)->not->toBeNull()
        ->and($forum->participation_role)->toBe(ScientificForumRole::Speaker->value)
        ->and($forum->scope)->toBe(PublicationScope::International->value);
});

test('scientific forums import stores custom role text and defaults blank scope to local', function () {
    $import = new ScientificForumsImport(1);

    $forum = $import->model([
        'title_ka' => 'კონფერენცია',
        'title_en' => 'Conference',
        'participation_role' => 'Chair',
        'scope' => '',
    ]);

    expect($forum)->not->toBeNull()
        ->and($forum->participation_role)->toBe('Chair')
        ->and($forum->scope)->toBe(PublicationScope::Local->value);
});

test('cv shows attendee and local labels for scientific forums', function () {
    app()->setLocale('ka');

    $byLabel = collect(scientificForumCvFields(new ScientificForum([
        'participation_role' => ScientificForumRole::Attendee->value,
        'scope' => PublicationScope::Local->value,
    ])))->pluck('value', 'label');

    expect($byLabel->get(__('filament.personal_file.scientific_forums.participation_role')))
        ->toBe(__('filament.personal_file.scientific_forums.participation_role_options.attendee'))
        ->and($byLabel->get(__('filament.personal_file.scientific_forums.scope')))
        ->toBe(__('filament.personal_file.publications.scope_options.local'));
});

test('cv shows custom other participation role as stored text', function () {
    app()->setLocale('ka');

    $byLabel = collect(scientificForumCvFields(new ScientificForum([
        'participation_role' => 'Chair',
        'scope' => PublicationScope::International->value,
    ])))->pluck('value', 'label');

    expect($byLabel->get(__('filament.personal_file.scientific_forums.participation_role')))->toBe('Chair')
        ->and($byLabel->get(__('filament.personal_file.scientific_forums.scope')))
        ->toBe(__('filament.personal_file.publications.scope_options.international'));
});

/**
 * @return list<array{label: string|null, value: string|null}>
 */
function scientificForumCvFields(ScientificForum $forum): array
{
    $employee = new Employee;
    $employee->setRelation('scientificForums', collect([$forum]));

    $method = new ReflectionMethod(EmployeeCvService::class, 'scientificForumsSection');

    return $method->invoke(new EmployeeCvService, $employee)['entries'][0]['fields'];
}
