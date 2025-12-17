<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Policies\DeclarationPolicy;
use AcMarche\Security\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->policy = new DeclarationPolicy();
});

describe('viewAny', function () {
    test('admin can view any declarations', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('ville role user can view any declarations', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('cpas role user can view any declarations', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('user without role cannot view any declarations', function () {
        $user = User::factory()->create();

        expect($this->policy->viewAny($user))->toBeFalse();
    });
});

describe('view', function () {
    test('admin can view any declaration', function () {
        $user = User::factory()->create(['username' => 'admin']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create(['user_add' => 'other_user']);

        expect($this->policy->view($user, $declaration))->toBeTrue();
    });

    test('owner can view their own declaration', function () {
        $user = User::factory()->create(['username' => 'owner']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create(['user_add' => 'owner']);

        // Add username accessor to declaration for testing
        $declaration->username = $user->username;

        expect($this->policy->view($user, $declaration))->toBeTrue();
    });

    test('non-owner cannot view others declaration', function () {
        $user = User::factory()->create(['username' => 'user1']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create(['user_add' => 'user2']);

        // Add username accessor to declaration for testing
        $declaration->username = 'user2';

        expect($this->policy->view($user, $declaration))->toBeFalse();
    });
});

describe('create', function () {
    test('admin can create declarations', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeTrue();
    });

    test('ville role user cannot create declarations', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeFalse();
    });

    test('cpas role user cannot create declarations', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeFalse();
    });

    test('user without role cannot create declarations', function () {
        $user = User::factory()->create();

        expect($this->policy->create($user))->toBeFalse();
    });
});

describe('update', function () {
    test('admin can update any declaration', function () {
        $user = User::factory()->create(['username' => 'admin']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create(['user_add' => 'other_user']);

        expect($this->policy->update($user, $declaration))->toBeTrue();
    });

    test('owner can update their own declaration', function () {
        $user = User::factory()->create(['username' => 'owner']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create(['user_add' => 'owner']);

        // Add username accessor to declaration for testing
        $declaration->username = $user->username;

        expect($this->policy->update($user, $declaration))->toBeTrue();
    });

    test('non-owner cannot update others declaration', function () {
        $user = User::factory()->create(['username' => 'user1']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create(['user_add' => 'user2']);

        // Add username accessor to declaration for testing
        $declaration->username = 'user2';

        expect($this->policy->update($user, $declaration))->toBeFalse();
    });
});

describe('delete', function () {
    test('admin can delete any declaration', function () {
        $user = User::factory()->create(['username' => 'admin']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create(['user_add' => 'other_user']);

        expect($this->policy->delete($user, $declaration))->toBeTrue();
    });

    test('owner can delete their own declaration', function () {
        $user = User::factory()->create(['username' => 'owner']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create(['user_add' => 'owner']);

        // Add username accessor to declaration for testing
        $declaration->username = $user->username;

        expect($this->policy->delete($user, $declaration))->toBeTrue();
    });

    test('non-owner cannot delete others declaration', function () {
        $user = User::factory()->create(['username' => 'user1']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create(['user_add' => 'user2']);

        // Add username accessor to declaration for testing
        $declaration->username = 'user2';

        expect($this->policy->delete($user, $declaration))->toBeFalse();
    });
});

describe('restore', function () {
    test('restore always returns false', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create();

        expect($this->policy->restore($user, $declaration))->toBeFalse();
    });
});

describe('forceDelete', function () {
    test('forceDelete always returns false', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create();

        expect($this->policy->forceDelete($user, $declaration))->toBeFalse();
    });
});
