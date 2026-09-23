<?php

use App\Enums\PublicationScope;
use App\Imports\PublicationsImport;
use App\Models\Publication;
use App\Services\EmployeeCvService;

test('publications import defaults publication details to local', function () {
    $import = new PublicationsImport(1);

    $publication = $import->model([
        'year' => 2024,
        'title_ka' => 'სტატია',
        'title_en' => 'Article',
        'authors_ka' => '',
        'authors_en' => '',
        'venue_ka' => '',
        'venue_en' => '',
    ]);

    expect($publication)->not->toBeNull()
        ->and($publication->publication_details)->toBe(Publication::defaultPublicationDetails())
        ->and($publication->publication_details['scope'])->toBe(PublicationScope::Local->value);
});

test('publications import maps scope indexed and impact factor from excel columns', function () {
    $import = new PublicationsImport(1);

    $publication = $import->model([
        'year' => 2024,
        'title_ka' => 'სტატია',
        'title_en' => 'Article',
        'scope' => 'საერთაშორისო',
        'indexed' => 'კი',
        'impact_factor' => '1',
    ]);

    expect($publication)->not->toBeNull()
        ->and($publication->publication_details)->toBe([
            'scope' => PublicationScope::International->value,
            'indexed' => true,
            'impact_factor' => true,
        ]);
});

test('publications import ignores indexed and impact factor unless the publication is international', function () {
    $import = new PublicationsImport(1);

    $publication = $import->model([
        'year' => 2024,
        'title_ka' => 'სტატია',
        'title_en' => 'Article',
        'scope' => 'ადგილობრივი',
        'indexed' => 'კი',
        'impact_factor' => 'კი',
    ]);

    expect($publication)->not->toBeNull()
        ->and($publication->publication_details)->toBe(Publication::defaultPublicationDetails());
});

test('publications import ignores impact factor unless the publication is indexed', function () {
    $import = new PublicationsImport(1);

    $publication = $import->model([
        'year' => 2024,
        'title_ka' => 'სტატია',
        'title_en' => 'Article',
        'scope' => 'international',
        'indexed' => '0',
        'impact_factor' => '1',
    ]);

    expect($publication)->not->toBeNull()
        ->and($publication->publication_details)->toBe([
            'scope' => PublicationScope::International->value,
            'indexed' => false,
            'impact_factor' => false,
        ]);
});

test('cv shows local type without indexed flags', function () {
    app()->setLocale('ka');

    $fields = publicationDetailFields(new Publication([
        'publication_details' => Publication::defaultPublicationDetails(),
    ]));

    expect(collect($fields)->pluck('value', 'label')->all())->toBe([
        __('filament.personal_file.publications.scope') => __('filament.personal_file.publications.scope_options.local'),
    ]);
});

test('cv shows indexed and impact factor for international publications', function () {
    app()->setLocale('ka');

    $fields = publicationDetailFields(new Publication([
        'publication_details' => [
            'scope' => PublicationScope::International->value,
            'indexed' => true,
            'impact_factor' => true,
        ],
    ]));

    expect(collect($fields)->pluck('value', 'label')->all())->toBe([
        __('filament.personal_file.publications.scope') => __('filament.personal_file.publications.scope_options.international'),
        __('filament.personal_file.publications.indexed') => __('filament.personal_file.publications.yes'),
        __('filament.personal_file.publications.impact_factor') => __('filament.personal_file.publications.yes'),
    ]);
});

test('cv hides impact factor unless the publication is indexed', function () {
    app()->setLocale('ka');

    $fields = publicationDetailFields(new Publication([
        'publication_details' => [
            'scope' => PublicationScope::International->value,
            'indexed' => false,
            'impact_factor' => true,
        ],
    ]));

    expect(collect($fields)->pluck('value', 'label')->all())->toBe([
        __('filament.personal_file.publications.scope') => __('filament.personal_file.publications.scope_options.international'),
        __('filament.personal_file.publications.indexed') => __('filament.personal_file.publications.no'),
    ]);
});

/**
 * @return list<array{label: string|null, value: string|null}>
 */
function publicationDetailFields(Publication $publication): array
{
    $method = new ReflectionMethod(EmployeeCvService::class, 'publicationDetailFields');

    return $method->invoke(new EmployeeCvService, $publication);
}
