<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\RolesEnum;
use AcMarche\Hrm\Filament\Pages\HrmDashboard;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
});

it('grants access to a GRH administrator', function (): void {
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_GRH_ADMIN->value]);
    $user = User::factory()->create(['is_administrator' => false]);
    $user->roles()->attach($role);

    $this->actingAs($user);

    expect(HrmDashboard::canAccess())->toBeTrue();
});

it('grants access to a super administrator', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    expect(HrmDashboard::canAccess())->toBeTrue();
});

it('denies access to a user without the GRH admin role', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => false]));

    expect(HrmDashboard::canAccess())->toBeFalse();
});
