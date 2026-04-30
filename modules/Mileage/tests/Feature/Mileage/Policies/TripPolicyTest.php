<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\Trip;
use AcMarche\Mileage\Policies\TripPolicy;
use AcMarche\Security\Models\Role;
use App\Models\User;

beforeEach(function (): void {
    $this->policy = new TripPolicy();
});

describe('viewAny', function (): void {
    test('admin can view any trips', function (): void {
        $user = User::factory()->create(['username' => 'admin']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('ville role user can view any trips', function (): void {
        $user = User::factory()->create(['username' => 'ville_user']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('cpas role user can view any trips', function (): void {
        $user = User::factory()->create(['username' => 'cpas_user']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('user without role cannot view any trips', function (): void {
        $user = User::factory()->create();

        expect($this->policy->viewAny($user))->toBeFalse();
    });
});

describe('view', function (): void {
    test('admin can view any trip', function (): void {
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

    test('owner can view their own trip', function (): void {
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

    test('non-owner cannot view others trip', function (): void {
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

    test('user without role cannot view any trip', function (): void {
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

describe('create', function (): void {
    test('admin can create trips', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeTrue();
    });

    test('ville role user can create trips', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeTrue();
    });

    test('cpas role user can create trips', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeTrue();
    });

    test('user without role cannot create trips', function (): void {
        $user = User::factory()->create();

        expect($this->policy->create($user))->toBeFalse();
    });
});

describe('update', function (): void {
    test('admin cannot update any trip', function (): void {
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

        expect($this->policy->update($user, $trip))->toBeFalse();
    });

    test('owner cannot update their own trip', function (): void {
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

        expect($this->policy->update($user, $trip))->toBeFalse();
    });

    test('non-owner cannot update others trip', function (): void {
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

describe('delete', function (): void {
    test('admin can delete any trip', function (): void {
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

    test('owner cannot delete their own trip declared', function (): void {
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

        expect($this->policy->delete($user, $trip))->toBeFalse();
    });

    test('owner can delete their own trip not declared', function (): void {
        $user = User::factory()->create(['username' => 'owner']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $trip = Trip::factory()->create([
            'user_id' => $user->id,
            'user_add' => 'owner',
        ]);

        // Add username accessor to trip for testing
        $trip->username = $user->username;

        expect($this->policy->delete($user, $trip))->toBeTrue();
    });

    test('non-owner cannot delete others trip', function (): void {
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

describe('restore', function (): void {
    test('restore always returns false', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create();
        $trip = Trip::factory()->create(['declaration_id' => $declaration->id]);

        expect($this->policy->restore($user, $trip))->toBeFalse();
    });
});

describe('forceDelete', function (): void {
    test('forceDelete always returns false', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $declaration = Declaration::factory()->create();
        $trip = Trip::factory()->create(['declaration_id' => $declaration->id]);

        expect($this->policy->forceDelete($user, $trip))->toBeFalse();
    });
});
