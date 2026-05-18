<?php

declare(strict_types=1);

use AcMarche\Security\Models\Role;
use AcMarche\StreetWatch\Enums\RolesEnum;
use AcMarche\StreetWatch\Filament\Resources\TypesIncident\Pages\CreateTypeIncident;
use AcMarche\StreetWatch\Filament\Resources\TypesIncident\Pages\ListTypesIncident;
use AcMarche\StreetWatch\Filament\Resources\TypesIncident\Pages\ViewTypeIncident;
use AcMarche\StreetWatch\Models\TypeIncident;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('street-watch-panel'));

    $this->member = User::factory()->create();
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_STREET_WATCH->value]);
    $this->member->roles()->attach($role);
});

it('renders the list and view pages for a member', function (): void {
    $this->actingAs($this->member);
    $type = TypeIncident::factory()->create();

    livewire(ListTypesIncident::class)->assertOk();
    livewire(ViewTypeIncident::class, ['record' => $type->id])->assertOk();
});

it('forbids creating a type for ROLE_STREET_WATCH-only members', function (): void {
    $this->actingAs($this->member);

    livewire(CreateTypeIncident::class)->assertForbidden();
});

it('hides the create header action for a ROLE_STREET_WATCH-only member', function (): void {
    $this->actingAs($this->member);

    livewire(ListTypesIncident::class)->assertActionHidden(CreateAction::class);
});

it('forbids list page for a user without the role', function (): void {
    $stranger = User::factory()->create();
    $this->actingAs($stranger);

    livewire(ListTypesIncident::class)->assertForbidden();
});

it('lets an administrator create a type', function (): void {
    $admin = User::factory()->create();
    $adminRole = Role::factory()->create(['name' => AcMarche\Security\Enums\RolesEnum::INTRANET_ADMIN->value]);
    $admin->roles()->attach($adminRole);
    $this->actingAs($admin);

    livewire(CreateTypeIncident::class)
        ->fillForm(['name' => 'Conflit'])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(TypeIncident::class, ['name' => 'Conflit']);
});

it('rejects a duplicate name when admin creates one', function (): void {
    $admin = User::factory()->create();
    $adminRole = Role::factory()->create(['name' => AcMarche\Security\Enums\RolesEnum::INTRANET_ADMIN->value]);
    $admin->roles()->attach($adminRole);
    $this->actingAs($admin);

    TypeIncident::factory()->create(['name' => 'duplicate']);

    livewire(CreateTypeIncident::class)
        ->fillForm(['name' => 'duplicate'])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique'])
        ->assertNotNotified();
});
