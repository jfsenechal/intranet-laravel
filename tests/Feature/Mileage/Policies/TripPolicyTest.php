<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\Trip;
use AcMarche\Mileage\Policies\TripPolicy;
use AcMarche\Security\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->policy = new TripPolicy();
});

describe('viewAny', function () {
    test('admin can view any trips', function () {
        $user = User::factory()->create(['username' => 'admin']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('ville role user can view any trips', function () {
        $user = User::factory()->create(['username' => 'ville_user']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('cpas role user can view any trips', function () {
        $user = User::factory()->create(['username' => 'cpas_user']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('user without role cannot view any trips', function () {
        $user = User::factory()->create();

        expect($this->policy->viewAny($user))->toBeFalse();
    });
});

describe('view', function () {
    test('admin can view any trip', function () {
        $user = User::factory()->create(['username' => 'admin']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $otherUser = User::factory()->create(['username' => 'other_user']);
        $declaration = Declaration::factory()->create(['user_add' => 'other_user']);
        $trip = Trip::factory()->create([
            'declaration_id' => $declaration->id,
            'user_id' => $otherUser->id,
            'user_add' => 'other_user',
        ]);

        expect($this->policy->view($user, $trip))->toBeTrue();
    });

    test('owner can view their own trip', function () {
        $user = User::factory()->create(['username' => 'owner']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create(['user_add' => 'owner']);
        $trip = Trip::factory()->create([
            'declaration_id' => $declaration->id,
            'user_id' => $user->id,
            'user_add' => 'owner',
        ]);

        // Add username accessor to trip for testing
        $trip->username = $user->username;

        expect($this->policy->view($user, $trip))->toBeTrue();
    });

    test('non-owner cannot view others trip', function () {
        $user = User::factory()->create(['username' => 'user1']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $otherUser = User::factory()->create(['username' => 'user2']);
        $declaration = Declaration::factory()->create(['user_add' => 'user2']);
        $trip = Trip::factory()->create([
            'declaration_id' => $declaration->id,
            'user_id' => $otherUser->id,
            'user_add' => 'user2',
        ]);

        // Add username accessor to trip for testing
        $trip->username = 'user2';

        expect($this->policy->view($user, $trip))->toBeFalse();
    });

    test('user without role cannot view any trip', function () {
        $user = User::factory()->create(['username' => 'no_role']);

        $otherUser = User::factory()->create(['username' => 'owner']);
        $declaration = Declaration::factory()->create(['user_add' => 'owner']);
        $trip = Trip::factory()->create([
            'declaration_id' => $declaration->id,
            'user_id' => $otherUser->id,
            'user_add' => 'owner',
        ]);

        // Add username accessor to trip for testing
        $trip->username = 'owner';

        expect($this->policy->view($user, $trip))->toBeFalse();
    });
});

describe('create', function () {
    test('admin can create trips', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeTrue();
    });

    test('ville role user can create trips', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeTrue();
    });

    test('cpas role user can create trips', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeTrue();
    });

    test('user without role cannot create trips', function () {
        $user = User::factory()->create();

        expect($this->policy->create($user))->toBeFalse();
    });
});

describe('update', function () {
    test('admin can update any trip', function () {
        $user = User::factory()->create(['username' => 'admin']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $otherUser = User::factory()->create(['username' => 'other_user']);
        $declaration = Declaration::factory()->create(['user_add' => 'other_user']);
        $trip = Trip::factory()->create([
            'declaration_id' => $declaration->id,
            'user_id' => $otherUser->id,
            'user_add' => 'other_user',
        ]);

        expect($this->policy->update($user, $trip))->toBeTrue();
    });

    test('owner can update their own trip', function () {
        $user = User::factory()->create(['username' => 'owner']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create(['user_add' => 'owner']);
        $trip = Trip::factory()->create([
            'declaration_id' => $declaration->id,
            'user_id' => $user->id,
            'user_add' => 'owner',
        ]);

        // Add username accessor to trip for testing
        $trip->username = $user->username;

        expect($this->policy->update($user, $trip))->toBeTrue();
    });

    test('non-owner cannot update others trip', function () {
        $user = User::factory()->create(['username' => 'user1']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $otherUser = User::factory()->create(['username' => 'user2']);
        $declaration = Declaration::factory()->create(['user_add' => 'user2']);
        $trip = Trip::factory()->create([
            'declaration_id' => $declaration->id,
            'user_id' => $otherUser->id,
            'user_add' => 'user2',
        ]);

        // Add username accessor to trip for testing
        $trip->username = 'user2';

        expect($this->policy->update($user, $trip))->toBeFalse();
    });
});

describe('delete', function () {
    test('admin can delete any trip', function () {
        $user = User::factory()->create(['username' => 'admin']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $otherUser = User::factory()->create(['username' => 'other_user']);
        $declaration = Declaration::factory()->create(['user_add' => 'other_user']);
        $trip = Trip::factory()->create([
            'declaration_id' => $declaration->id,
            'user_id' => $otherUser->id,
            'user_add' => 'other_user',
        ]);

        expect($this->policy->delete($user, $trip))->toBeTrue();
    });

    test('owner can delete their own trip', function () {
        $user = User::factory()->create(['username' => 'owner']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create(['user_add' => 'owner']);
        $trip = Trip::factory()->create([
            'declaration_id' => $declaration->id,
            'user_id' => $user->id,
            'user_add' => 'owner',
        ]);

        // Add username accessor to trip for testing
        $trip->username = $user->username;

        expect($this->policy->delete($user, $trip))->toBeTrue();
    });

    test('non-owner cannot delete others trip', function () {
        $user = User::factory()->create(['username' => 'user1']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $otherUser = User::factory()->create(['username' => 'user2']);
        $declaration = Declaration::factory()->create(['user_add' => 'user2']);
        $trip = Trip::factory()->create([
            'declaration_id' => $declaration->id,
            'user_id' => $otherUser->id,
            'user_add' => 'user2',
        ]);

        // Add username accessor to trip for testing
        $trip->username = 'user2';

        expect($this->policy->delete($user, $trip))->toBeFalse();
    });
});

describe('restore', function () {
    test('restore always returns false', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create();
        $trip = Trip::factory()->create(['declaration_id' => $declaration->id]);

        expect($this->policy->restore($user, $trip))->toBeFalse();
    });
});

describe('forceDelete', function () {
    test('forceDelete always returns false', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create();
        $trip = Trip::factory()->create(['declaration_id' => $declaration->id]);

        expect($this->policy->forceDelete($user, $trip))->toBeFalse();
    });
});
