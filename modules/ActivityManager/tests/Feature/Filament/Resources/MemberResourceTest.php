<?php

declare(strict_types=1);

use AcMarche\ActivityManager\Enums\CiviliteEnum;
use AcMarche\ActivityManager\Enums\RolesEnum;
use AcMarche\ActivityManager\Filament\Resources\Members\Pages\CreateMember;
use AcMarche\ActivityManager\Filament\Resources\Members\Pages\EditMember;
use AcMarche\ActivityManager\Filament\Resources\Members\Pages\ListMembers;
use AcMarche\ActivityManager\Filament\Resources\Members\Pages\ViewMember;
use AcMarche\ActivityManager\Models\Member;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('activity-manager-panel'));

    $this->admin = User::factory()->create(['is_administrator' => true]);

    $this->mdaAdmin = User::factory()->create();
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_MDA_ADMIN->value]);
    $this->mdaAdmin->roles()->attach($role);

    $this->actingAs($this->admin);
});

it('renders list, create, view and edit pages', function (): void {
    $member = Member::factory()->create();

    livewire(ListMembers::class)->assertOk();
    livewire(CreateMember::class)->assertOk();
    livewire(ViewMember::class, ['record' => $member->id])->assertOk();
    livewire(EditMember::class, ['record' => $member->id])->assertOk();
});

it('lists members', function (): void {
    $members = Member::factory(3)->create();

    livewire(ListMembers::class)
        ->loadTable()
        ->assertCanSeeTableRecords($members);
});

it('creates a member via the form', function (): void {
    livewire(CreateMember::class)
        ->fillForm([
            'civility' => CiviliteEnum::MADAME->value,
            'last_name' => 'Dupont',
            'first_name' => 'Marie',
            'email' => 'marie.dupont@example.com',
            'enabled' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Member::class, [
        'last_name' => 'Dupont',
        'first_name' => 'Marie',
        'email' => 'marie.dupont@example.com',
        'civility' => 'Madame',
    ]);
});

it('updates a member via the form', function (): void {
    $member = Member::factory()->create(['enabled' => true]);

    livewire(EditMember::class, ['record' => $member->id])
        ->fillForm(['enabled' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Member::class, [
        'id' => $member->id,
        'enabled' => false,
    ]);
});

it('validates required fields', function (array $data, array $errors): void {
    livewire(CreateMember::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`last_name` required' => [['last_name' => null, 'first_name' => 'X'], ['last_name' => 'required']],
    '`first_name` required' => [['last_name' => 'X', 'first_name' => null], ['first_name' => 'required']],
    '`email` invalid' => [
        ['last_name' => 'X', 'first_name' => 'Y', 'email' => 'not-an-email'],
        ['email' => 'email'],
    ],
]);

it('forbids a stranger from listing', function (): void {
    $this->actingAs(User::factory()->create());

    livewire(ListMembers::class)->assertForbidden();
});
