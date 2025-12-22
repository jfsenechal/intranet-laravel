<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Models\PersonalInformation;
use AcMarche\Mileage\Policies\PersonalInformationPolicy;
use AcMarche\Security\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->policy = new PersonalInformationPolicy();
});

describe('viewAny', function () {
    test('admin can view any personal information', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('ville role user can view any personal information', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('cpas role user can view any personal information', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('user without role cannot view any personal information', function () {
        $user = User::factory()->create();

        expect($this->policy->viewAny($user))->toBeFalse();
    });
});

describe('view', function () {
    test('admin can view any personal information', function () {
        $user = User::factory()->create(['username' => 'admin']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $personalInfo = PersonalInformation::factory()->create(['username' => 'other_user']);

        expect($this->policy->view($user, $personalInfo))->toBeTrue();
    });

    test('owner can view their own personal information', function () {
        $user = User::factory()->create(['username' => 'owner']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $personalInfo = PersonalInformation::factory()->create(['username' => 'owner']);

        expect($this->policy->view($user, $personalInfo))->toBeTrue();
    });

    test('non-owner cannot view others personal information', function () {
        $user = User::factory()->create(['username' => 'user1']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $personalInfo = PersonalInformation::factory()->create(['username' => 'user2']);

        expect($this->policy->view($user, $personalInfo))->toBeFalse();
    });
});

describe('create', function () {
    test('admin cannot create personal information', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeFalse();
    });

    test('ville role user cannot create personal information', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeFalse();
    });

    test('cpas role user cannot create personal information', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeFalse();
    });

    test('user without role cannot create personal information', function () {
        $user = User::factory()->create();

        expect($this->policy->create($user))->toBeFalse();
    });
});

describe('update', function () {
    test('admin can update any personal information', function () {
        $user = User::factory()->create(['username' => 'admin']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $personalInfo = PersonalInformation::factory()->create(['username' => 'other_user']);

        expect($this->policy->update($user, $personalInfo))->toBeTrue();
    });

    test('owner can update their own personal information', function () {
        $user = User::factory()->create(['username' => 'owner']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $personalInfo = PersonalInformation::factory()->create(['username' => 'owner']);

        expect($this->policy->update($user, $personalInfo))->toBeTrue();
    });

    test('non-owner cannot update others personal information', function () {
        $user = User::factory()->create(['username' => 'user1']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $personalInfo = PersonalInformation::factory()->create(['username' => 'user2']);

        expect($this->policy->update($user, $personalInfo))->toBeFalse();
    });
});

describe('delete', function () {
    test('admin cannot delete any personal information', function () {
        $user = User::factory()->create(['username' => 'admin']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $personalInfo = PersonalInformation::factory()->create(['username' => 'other_user']);

        expect($this->policy->delete($user, $personalInfo))->toBeFalse();
    });

    test('owner cannot delete their own personal information', function () {
        $user = User::factory()->create(['username' => 'owner']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $personalInfo = PersonalInformation::factory()->create(['username' => 'owner']);

        expect($this->policy->delete($user, $personalInfo))->toBeFalse();
    });

    test('non-owner cannot delete others personal information', function () {
        $user = User::factory()->create(['username' => 'user1']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $personalInfo = PersonalInformation::factory()->create(['username' => 'user2']);

        expect($this->policy->delete($user, $personalInfo))->toBeFalse();
    });
});

describe('restore', function () {
    test('restore always returns false', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $personalInfo = PersonalInformation::factory()->create();

        expect($this->policy->restore($user, $personalInfo))->toBeFalse();
    });
});

describe('forceDelete', function () {
    test('forceDelete always returns false', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $personalInfo = PersonalInformation::factory()->create();

        expect($this->policy->forceDelete($user, $personalInfo))->toBeFalse();
    });
});
