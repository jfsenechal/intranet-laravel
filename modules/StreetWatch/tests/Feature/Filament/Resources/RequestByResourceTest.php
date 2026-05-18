<?php

declare(strict_types=1);

use AcMarche\Security\Models\Role;
use AcMarche\StreetWatch\Enums\RolesEnum;
use AcMarche\StreetWatch\Filament\Resources\RequestsBy\Pages\CreateRequestBy;
use AcMarche\StreetWatch\Filament\Resources\RequestsBy\Pages\EditRequestBy;
use AcMarche\StreetWatch\Filament\Resources\RequestsBy\Pages\ListRequestsBy;
use AcMarche\StreetWatch\Filament\Resources\RequestsBy\Pages\ViewRequestBy;
use AcMarche\StreetWatch\Models\RequestBy;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('street-watch-panel'));

    $this->member = User::factory()->create();
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_STREET_WATCH->value]);
    $this->member->roles()->attach($role);
});

it('renders the list, view and edit pages for a member', function (): void {
    $this->actingAs($this->member);
    $requestBy = RequestBy::factory()->create();

    livewire(ListRequestsBy::class)->assertOk();
    livewire(ViewRequestBy::class, ['record' => $requestBy->id])->assertOk();
});

it('lists requests_by', function (): void {
    $this->actingAs($this->member);
    $rows = RequestBy::factory(3)->create();

    livewire(ListRequestsBy::class)
        ->loadTable()
        ->assertCanSeeTableRecords($rows);
});

it('forbids creating a RequestBy for ROLE_STREET_WATCH-only members', function (): void {
    $this->actingAs($this->member);

    livewire(CreateRequestBy::class)->assertForbidden();
});

it('hides the create header action for a ROLE_STREET_WATCH-only member', function (): void {
    $this->actingAs($this->member);

    livewire(ListRequestsBy::class)->assertActionHidden(CreateAction::class);
});

it('forbids list page for a user without the role', function (): void {
    $stranger = User::factory()->create();
    $this->actingAs($stranger);

    livewire(ListRequestsBy::class)->assertForbidden();
});

it('lets an administrator create a RequestBy', function (): void {
    $admin = User::factory()->create();
    $adminRole = Role::factory()->create(['name' => AcMarche\Security\Enums\RolesEnum::INTRANET_ADMIN->value]);
    $admin->roles()->attach($adminRole);
    $this->actingAs($admin);

    livewire(CreateRequestBy::class)
        ->fillForm(['name' => 'Service jeunesse'])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(RequestBy::class, ['name' => 'Service jeunesse']);
});

it('rejects a duplicate name when admin creates one', function (): void {
    $admin = User::factory()->create();
    $adminRole = Role::factory()->create(['name' => AcMarche\Security\Enums\RolesEnum::INTRANET_ADMIN->value]);
    $admin->roles()->attach($adminRole);
    $this->actingAs($admin);

    RequestBy::factory()->create(['name' => 'duplicate']);

    livewire(CreateRequestBy::class)
        ->fillForm(['name' => 'duplicate'])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique'])
        ->assertNotNotified();
});

it('validates the name field', function (array $data, array $errors): void {
    $admin = User::factory()->create();
    $adminRole = Role::factory()->create(['name' => AcMarche\Security\Enums\RolesEnum::INTRANET_ADMIN->value]);
    $admin->roles()->attach($adminRole);
    $this->actingAs($admin);

    $requestBy = RequestBy::factory()->create();

    livewire(EditRequestBy::class, ['record' => $requestBy->id])
        ->fillForm([
            'name' => 'Valid name',
            ...$data,
        ])
        ->call('save')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
]);
