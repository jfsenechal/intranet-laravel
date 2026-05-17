<?php

declare(strict_types=1);

use AcMarche\CpasLibrary\Enums\RolesEnum;
use AcMarche\CpasLibrary\Models\Categorie;
use AcMarche\CpasLibrary\Models\Fiche;
use AcMarche\CpasLibrary\Models\Tag;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
    $adminRole = Role::factory()->create(['name' => RolesEnum::ROLE_LIBRARY_ADMIN->value]);
    $this->admin->roles()->attach($adminRole);

    $this->member = User::factory()->create(['username' => 'member1']);
    $memberRole = Role::factory()->create(['name' => RolesEnum::ROLE_LIBRARY->value]);
    $this->member->roles()->attach($memberRole);

    $this->stranger = User::factory()->create();
});

it('grants viewAny on Categorie to library users', function (): void {
    expect(Gate::forUser($this->admin)->allows('viewAny', Categorie::class))->toBeTrue();
    expect(Gate::forUser($this->member)->allows('viewAny', Categorie::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('viewAny', Categorie::class))->toBeFalse();
});

it('grants create on Categorie only to admins', function (): void {
    expect(Gate::forUser($this->admin)->allows('create', Categorie::class))->toBeTrue();
    expect(Gate::forUser($this->member)->allows('create', Categorie::class))->toBeFalse();
});

it('grants viewAny on Tag to library users', function (): void {
    expect(Gate::forUser($this->admin)->allows('viewAny', Tag::class))->toBeTrue();
    expect(Gate::forUser($this->member)->allows('viewAny', Tag::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('viewAny', Tag::class))->toBeFalse();
});

it('grants create on Tag only to admins', function (): void {
    expect(Gate::forUser($this->admin)->allows('create', Tag::class))->toBeTrue();
    expect(Gate::forUser($this->member)->allows('create', Tag::class))->toBeFalse();
});

it('grants viewAny on Fiche to library users', function (): void {
    expect(Gate::forUser($this->admin)->allows('viewAny', Fiche::class))->toBeTrue();
    expect(Gate::forUser($this->member)->allows('viewAny', Fiche::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('viewAny', Fiche::class))->toBeFalse();
});

it('lets a member update their own fiche but not someone elses', function (): void {
    $mine = Fiche::factory()->create(['userAdd' => 'member1']);
    $theirs = Fiche::factory()->create(['userAdd' => 'someone-else']);

    expect(Gate::forUser($this->member)->allows('update', $mine))->toBeTrue();
    expect(Gate::forUser($this->member)->allows('update', $theirs))->toBeFalse();
});

it('lets an admin update any fiche', function (): void {
    $someones = Fiche::factory()->create(['userAdd' => 'someone-else']);

    expect(Gate::forUser($this->admin)->allows('update', $someones))->toBeTrue();
});

it('lets a member delete their own fiche but not someone elses', function (): void {
    $mine = Fiche::factory()->create(['userAdd' => 'member1']);
    $theirs = Fiche::factory()->create(['userAdd' => 'someone-else']);

    expect(Gate::forUser($this->member)->allows('delete', $mine))->toBeTrue();
    expect(Gate::forUser($this->member)->allows('delete', $theirs))->toBeFalse();
});
