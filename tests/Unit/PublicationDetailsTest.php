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
