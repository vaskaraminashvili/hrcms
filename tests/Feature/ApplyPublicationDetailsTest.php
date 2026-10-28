<?php

use App\Enums\PublicationScope;
use App\Models\Employee;
use App\Models\Publication;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

function employeeForPublicationDetails(string $personalNumber): Employee
{
    return Employee::query()->create([
        'name' => 'Nino',
        'surname' => 'Beridze',
        'personal_number' => $personalNumber,
        'birth_date' => '1990-01-01',
    ]);
}

uses(LazilyRefreshDatabase::class);

test('publication details normalization drops indexed flags unless the publication is international and indexed', function () {
    expect(Publication::normalizePublicationDetails(PublicationScope::Local, true, true))
        ->toBe(Publication::defaultPublicationDetails())
        ->and(Publication::normalizePublicationDetails(PublicationScope::International, false, true))
        ->toBe([
            'scope' => PublicationScope::International->value,
            'indexed' => false,
            'impact_factor' => false,
        ])
        ->and(Publication::normalizePublicationDetails('international', true, true))
        ->toBe([
            'scope' => PublicationScope::International->value,
            'indexed' => true,
            'impact_factor' => true,
        ]);
});

test('applying publication details updates only the selected publications for that employee', function () {
    $employee = employeeForPublicationDetails('01001001001');
    $otherEmployee = employeeForPublicationDetails('01001001002');

    $selected = Publication::factory()->create([
        'employee_id' => $employee->id,
        'publication_details' => [
            'scope' => PublicationScope::International->value,
            'indexed' => true,
            'impact_factor' => true,
        ],
    ]);
    $untouched = Publication::factory()->create([
        'employee_id' => $employee->id,
    ]);
    $foreign = Publication::factory()->create([
        'employee_id' => $otherEmployee->id,
    ]);

    $local = Publication::normalizePublicationDetails(PublicationScope::Local, true, true);

    $updated = Publication::applyPublicationDetails(
        $employee->id,
        [$selected->id, $foreign->id],
        $local,
    );

    expect($updated)->toBe(1)
        ->and($selected->refresh()->publication_details)->toBe(Publication::defaultPublicationDetails())
        ->and($untouched->refresh()->publication_details)->toBe(Publication::defaultPublicationDetails())
        ->and($foreign->refresh()->publication_details)->toBe(Publication::defaultPublicationDetails());

    $international = Publication::normalizePublicationDetails(PublicationScope::International, true, false);

    Publication::applyPublicationDetails($employee->id, [$selected->id], $international);

    expect($selected->refresh()->publication_details)->toBe([
        'scope' => PublicationScope::International->value,
        'indexed' => true,
        'impact_factor' => false,
    ]);
});
