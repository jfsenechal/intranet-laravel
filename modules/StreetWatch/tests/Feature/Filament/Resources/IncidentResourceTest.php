<?php

declare(strict_types=1);

use AcMarche\Security\Models\Role;
use AcMarche\StreetWatch\Enums\RolesEnum;
use AcMarche\StreetWatch\Filament\Resources\Incidents\Pages\CreateIncident;
use AcMarche\StreetWatch\Filament\Resources\Incidents\Pages\EditIncident;
use AcMarche\StreetWatch\Filament\Resources\Incidents\Pages\ListIncidents;
use AcMarche\StreetWatch\Filament\Resources\Incidents\Pages\ViewIncident;
use AcMarche\StreetWatch\Models\Incident;
use AcMarche\StreetWatch\Models\RequestBy;
use AcMarche\StreetWatch\Models\TypeIncident;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('street-watch-panel'));

    $this->member = User::factory()->create(['username' => 'member1']);
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_STREET_WATCH->value]);
    $this->member->roles()->attach($role);

    $this->actingAs($this->member);
});

it('renders list, create, view and edit pages', function (): void {
    $incident = Incident::factory()->create(['user_add' => 'member1']);

    livewire(ListIncidents::class)->assertOk();
    livewire(CreateIncident::class)->assertOk();
    livewire(ViewIncident::class, ['record' => $incident->id])->assertOk();
    livewire(EditIncident::class, ['record' => $incident->id])->assertOk();
});

it('lists incidents', function (): void {
    $incidents = Incident::factory(3)->create();

    livewire(ListIncidents::class)
        ->loadTable()
        ->assertCanSeeTableRecords($incidents);
});

it('creates an incident and auto-fills user_add', function (): void {
    $requestBy = RequestBy::factory()->create();
    $type = TypeIncident::factory()->create();

    livewire(CreateIncident::class)
        ->fillForm([
            'place' => 'Place Roi Albert',
            'object' => 'Attroupement',
            'description' => 'Trois jeunes bloquent l\'entrée',
            'response' => null,
            'requestBy_id' => $requestBy->id,
            'typeIncident_id' => $type->id,
            'occurred_date' => now()->toDateTimeString(),
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Incident::class, [
        'place' => 'Place Roi Albert',
        'object' => 'Attroupement',
        'user_add' => 'member1',
    ]);
});

it('updates an incident the member authored', function (): void {
    $incident = Incident::factory()->create(['user_add' => 'member1']);

    livewire(EditIncident::class, ['record' => $incident->id])
        ->fillForm(['object' => 'Objet modifié'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Incident::class, [
        'id' => $incident->id,
        'object' => 'Objet modifié',
    ]);
});

it('forbids editing an incident the member did not author', function (): void {
    $incident = Incident::factory()->create(['user_add' => 'someone-else']);

    livewire(EditIncident::class, ['record' => $incident->id])->assertForbidden();
});

it('forbids list page for a user without the role', function (): void {
    $stranger = User::factory()->create();
    $this->actingAs($stranger);

    livewire(ListIncidents::class)->assertForbidden();
});

it('validates the form data', function (array $data, array $errors): void {
    $requestBy = RequestBy::factory()->create();
    $type = TypeIncident::factory()->create();

    livewire(CreateIncident::class)
        ->fillForm([
            'place' => 'Place Roi Albert',
            'object' => 'Attroupement',
            'description' => 'Description suffisamment longue',
            'requestBy_id' => $requestBy->id,
            'typeIncident_id' => $type->id,
            ...$data,
        ])
        ->call('create')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`place` is required' => [['place' => null], ['place' => 'required']],
    '`place` is max 255 characters' => [['place' => Str::random(256)], ['place' => 'max']],
    '`object` is required' => [['object' => null], ['object' => 'required']],
    '`object` is max 255 characters' => [['object' => Str::random(256)], ['object' => 'max']],
    '`description` is required' => [['description' => null], ['description' => 'required']],
    '`requestBy_id` is required' => [['requestBy_id' => null], ['requestBy_id' => 'required']],
    '`typeIncident_id` is required' => [['typeIncident_id' => null], ['typeIncident_id' => 'required']],
]);

it('deletes an incident from the view page', function (): void {
    $incident = Incident::factory()->create(['user_add' => 'member1']);

    livewire(ViewIncident::class, ['record' => $incident->id])
        ->callAction(\Filament\Actions\DeleteAction::class)
        ->assertNotified();

    assertDatabaseMissing($incident);
});
