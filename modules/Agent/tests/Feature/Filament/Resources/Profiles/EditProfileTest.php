<?php

declare(strict_types=1);

use AcMarche\Agent\Enums\RolesEnum;
use AcMarche\Agent\Filament\Resources\Profiles\Pages\EditProfile;
use AcMarche\Agent\Models\Profile;
use AcMarche\Agent\Models\Share;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('agent-panel'));
    $this->agentRole = Role::factory()->create(['name' => RolesEnum::ROLE_AGENT->value]);
});

function agentUser(Role $role, string $email): User
{
    $user = User::factory()->create(['is_administrator' => false, 'email' => $email]);
    $user->roles()->attach($role);

    return $user;
}

it('lets an agent administrator edit any profile', function (): void {
    $adminRole = Role::factory()->create(['name' => RolesEnum::ROLE_AGENT_ADMIN->value]);
    $admin = User::factory()->create(['is_administrator' => false]);
    $admin->roles()->attach($adminRole);

    expect($admin->can('update', Profile::factory()->create()))->toBeTrue();
});

it('lets a delegate edit the profile shared with them', function (): void {
    $delegate = agentUser($this->agentRole, 'delegate@marche.be');
    $profile = Profile::factory()->create();
    $profile->shares()->save(new Share(['shared_by' => 'admin', 'shared_for' => 'delegate@marche.be']));

    expect($delegate->can('update', $profile))->toBeTrue();
});

it('does not let a delegate edit a profile shared with somebody else', function (): void {
    $delegate = agentUser($this->agentRole, 'delegate@marche.be');
    $profile = Profile::factory()->create();
    $profile->shares()->save(new Share(['shared_by' => 'admin', 'shared_for' => 'other@marche.be']));

    expect($delegate->can('update', $profile))->toBeFalse();
});

it('does not let an agent without any share edit a profile', function (): void {
    $agent = agentUser($this->agentRole, 'agent@marche.be');

    expect($agent->can('update', Profile::factory()->create()))->toBeFalse();
});

it('keeps record-less update checks reserved to administrators', function (): void {
    $agent = agentUser($this->agentRole, 'agent@marche.be');
    $profile = Profile::factory()->create();
    $profile->shares()->save(new Share(['shared_by' => 'admin', 'shared_for' => 'agent@marche.be']));

    expect($agent->can('update', Profile::class))->toBeFalse();
});

it('opens the edit page for a delegate', function (): void {
    $delegate = agentUser($this->agentRole, 'delegate@marche.be');
    $profile = Profile::factory()->create();
    $profile->shares()->save(new Share(['shared_by' => 'admin', 'shared_for' => 'delegate@marche.be']));

    $this->actingAs($delegate)
        ->get(EditProfile::getUrl(['record' => $profile->getKey()], panel: 'agent-panel'))
        ->assertSuccessful();
});

it('forbids the edit page for an agent the profile was not shared with', function (): void {
    $agent = agentUser($this->agentRole, 'agent@marche.be');
    $profile = Profile::factory()->create();

    $this->actingAs($agent)
        ->get(EditProfile::getUrl(['record' => $profile->getKey()], panel: 'agent-panel'))
        ->assertForbidden();
});
