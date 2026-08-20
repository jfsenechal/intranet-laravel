<?php

declare(strict_types=1);

use AcMarche\Courrier\Filament\Resources\Services\ServiceResource;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use STS\FilamentImpersonate\ImpersonateManager;

/**
 * Impersonation must evaluate policies against the impersonated user. If the
 * impersonator's privileges leaked through, an administrator would silently
 * carry full rights into every session they open.
 */
beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));

    $this->administrator = User::factory()->create(['is_administrator' => true]);
    $this->target = User::factory()->create(['is_administrator' => false]);
    $this->target->roles()->attach(Role::factory()->create(['name' => 'ROLE_GRH_ADMIN']));
});

test('an administrator loses their own rights while impersonating a regular user', function (): void {
    $this->actingAs($this->administrator);

    expect(ServiceResource::can('deleteAny'))->toBeTrue();

    app(ImpersonateManager::class)->enter($this->administrator, $this->target);

    expect(auth()->id())->toBe($this->target->getKey())
        ->and(auth()->user()->isAdministrator())->toBeFalse()
        ->and(ServiceResource::can('deleteAny'))->toBeFalse()
        ->and(ServiceResource::can('create'))->toBeFalse();
});

test('leaving impersonation restores the administrator rights', function (): void {
    $this->actingAs($this->administrator);

    $manager = app(ImpersonateManager::class);
    $manager->enter($this->administrator, $this->target);
    $manager->leave();

    expect(auth()->id())->toBe($this->administrator->getKey())
        ->and(ServiceResource::can('deleteAny'))->toBeTrue();
});
