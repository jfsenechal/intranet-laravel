<?php

declare(strict_types=1);

use AcMarche\Conseil\Enums\RolesEnum;
use AcMarche\Conseil\Filament\Pages\NotifyRecipients;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('conseil-panel'));
});

it('grants access to a conseil admin', function (): void {
    $user = User::factory()->create(['is_administrator' => false]);
    $user->roles()->attach(Role::factory()->create(['name' => RolesEnum::ROLE_CONSEIL_ADMIN->value]));
    $this->actingAs($user);

    expect(NotifyRecipients::canAccess())->toBeTrue();

    livewire(NotifyRecipients::class)->assertOk();
});

it('grants access to a global administrator', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    expect(NotifyRecipients::canAccess())->toBeTrue();
});

it('forbids a user without the conseil admin role', function (): void {
    $user = User::factory()->create(['is_administrator' => false]);
    $user->roles()->attach(Role::factory()->create(['name' => 'ROLE_OTHER']));
    $this->actingAs($user);

    expect(NotifyRecipients::canAccess())->toBeFalse();

    livewire(NotifyRecipients::class)->assertForbidden();
});
