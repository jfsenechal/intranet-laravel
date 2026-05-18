<?php

declare(strict_types=1);

use AcMarche\College\Enums\RolesEnum;
use AcMarche\College\Models\Destinataire;
use AcMarche\College\Models\Notification;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

beforeEach(function (): void {
    $this->admin = User::factory()->create(['is_administrator' => true]);

    $this->convocation = User::factory()->create();
    $convocationRole = Role::factory()->create(['name' => RolesEnum::ROLE_COLLEGE_CONVOCATION->value]);
    $this->convocation->roles()->attach($convocationRole);

    $this->stranger = User::factory()->create();
});

it('grants viewAny on Destinataire to role holders and admins', function (): void {
    expect(Gate::forUser($this->admin)->allows('viewAny', Destinataire::class))->toBeTrue();
    expect(Gate::forUser($this->convocation)->allows('viewAny', Destinataire::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('viewAny', Destinataire::class))->toBeFalse();
});

it('grants create on Destinataire to role holders and admins', function (): void {
    expect(Gate::forUser($this->admin)->allows('create', Destinataire::class))->toBeTrue();
    expect(Gate::forUser($this->convocation)->allows('create', Destinataire::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('create', Destinataire::class))->toBeFalse();
});

it('grants viewAny on Notification to role holders and admins', function (): void {
    expect(Gate::forUser($this->admin)->allows('viewAny', Notification::class))->toBeTrue();
    expect(Gate::forUser($this->convocation)->allows('viewAny', Notification::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('viewAny', Notification::class))->toBeFalse();
});

it('grants create on Notification to role holders and admins', function (): void {
    expect(Gate::forUser($this->admin)->allows('create', Notification::class))->toBeTrue();
    expect(Gate::forUser($this->convocation)->allows('create', Notification::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('create', Notification::class))->toBeFalse();
});

it('forbids restore and forceDelete for everyone', function (): void {
    expect(Gate::forUser($this->admin)->allows('restore', Destinataire::class))->toBeFalse();
    expect(Gate::forUser($this->admin)->allows('forceDelete', Destinataire::class))->toBeFalse();
    expect(Gate::forUser($this->admin)->allows('restore', Notification::class))->toBeFalse();
    expect(Gate::forUser($this->admin)->allows('forceDelete', Notification::class))->toBeFalse();
});
