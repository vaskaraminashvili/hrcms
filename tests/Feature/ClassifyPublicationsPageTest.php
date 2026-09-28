<?php

use App\Enums\PublicationScope;
use App\Filament\Resources\Employees\Pages\ClassifyPublications;
use App\Models\Employee;
use App\Models\Publication;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

uses(LazilyRefreshDatabase::class);

test('each publication can be classified on its own and the save notifies when it finishes', function () {
    app()->setLocale('en');
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $user = User::factory()->create();
    Permission::findOrCreate('ViewAny:Employee');
    Permission::findOrCreate('Update:Employee');
    $user->givePermissionTo(['ViewAny:Employee', 'Update:Employee']);
    Livewire::actingAs($user);

    $employee = Employee::query()->create([
        'name' => 'Nino',
        'surname' => 'Beridze',
        'personal_number' => '01001001996',
        'birth_date' => '1990-01-01',
    ]);
    $selected = Publication::factory()->create([
        'employee_id' => $employee->id,
        'title' => ['ka' => 'არჩეული ნაშრომი', 'en' => 'Selected paper'],
    ]);
    $untouched = Publication::factory()->create([
        'employee_id' => $employee->id,
        'title' => ['ka' => 'სხვა ნაშრომი', 'en' => 'Other paper'],
    ]);

    // APP_URL has no scheme, which makes Livewire's test endpoint 404.
    config(['app.url' => 'http://localhost']);
    URL::forceRootUrl('http://localhost');

    Livewire::test(ClassifyPublications::class, ['record' => $employee->id])
        ->assertSee('Selected paper')
        ->assertSee('Other paper')
        ->assertSee(__('filament.save'))
        ->set('data.items.'.$selected->id.'.scope', PublicationScope::International->value)
        ->set('data.items.'.$selected->id.'.indexed', true)
        ->set('data.items.'.$selected->id.'.impact_factor', true)
        ->call('savePublication', $selected->id)
        ->call('refreshPublicationSaveStatus')
        ->assertNotified(__('filament.personal_file.publications.classify_saved'));

    expect($selected->refresh()->publication_details)->toBe([
        'scope' => PublicationScope::International->value,
        'indexed' => true,
        'impact_factor' => true,
    ])->and($untouched->refresh()->publication_details)->toBe(Publication::defaultPublicationDetails());
});
