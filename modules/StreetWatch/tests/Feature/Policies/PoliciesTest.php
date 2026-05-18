<?php

declare(strict_types=1);

use AcMarche\Security\Models\Role;
use AcMarche\StreetWatch\Enums\RolesEnum;
use AcMarche\StreetWatch\Models\Incident;
use AcMarche\StreetWatch\Models\RequestBy;
use AcMarche\StreetWatch\Models\TypeIncident;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

beforeEach(function (): void {
    $this->member = User::factory()->create(['username' => 'member1']);
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_STREET_WATCH->value]);
    $this->member->roles()->attach($role);

    $this->stranger = User::factory()->create();
});

it('grants viewAny on Incident to street-watch users', function (): void {
    expect(Gate::forUser($this->member)->allows('viewAny', Incident::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('viewAny', Incident::class))->toBeFalse();
});

it('grants create on Incident to street-watch users', function (): void {
    expect(Gate::forUser($this->member)->allows('create', Incident::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('create', Incident::class))->toBeFalse();
});

it('lets a member update their own incident but not someone elses', function (): void {
    $mine = Incident::factory()->create(['user_add' => 'member1']);
    $theirs = Incident::factory()->create(['user_add' => 'someone-else']);

    expect(Gate::forUser($this->member)->allows('update', $mine))->toBeTrue();
    expect(Gate::forUser($this->member)->allows('update', $theirs))->toBeFalse();
});

it('lets a member delete their own incident but not someone elses', function (): void {
    $mine = Incident::factory()->create(['user_add' => 'member1']);
    $theirs = Incident::factory()->create(['user_add' => 'someone-else']);

    expect(Gate::forUser($this->member)->allows('delete', $mine))->toBeTrue();
    expect(Gate::forUser($this->member)->allows('delete', $theirs))->toBeFalse();
});

it('grants viewAny on RequestBy to street-watch users', function (): void {
    expect(Gate::forUser($this->member)->allows('viewAny', RequestBy::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('viewAny', RequestBy::class))->toBeFalse();
});

it('denies create on RequestBy to non-administrator members', function (): void {
    expect(Gate::forUser($this->member)->allows('create', RequestBy::class))->toBeFalse();
});

it('grants viewAny on TypeIncident to street-watch users', function (): void {
    expect(Gate::forUser($this->member)->allows('viewAny', TypeIncident::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('viewAny', TypeIncident::class))->toBeFalse();
});

it('denies create on TypeIncident to non-administrator members', function (): void {
    expect(Gate::forUser($this->member)->allows('create', TypeIncident::class))->toBeFalse();
});
