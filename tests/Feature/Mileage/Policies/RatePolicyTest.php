<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Policies\RatePolicy;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Override model connections for testing
    config(['database.connections.maria-mileage' => config('database.connections.sqlite')]);
    config(['database.connections.mariadb' => config('database.connections.sqlite')]);

    $this->policy = new RatePolicy();
});

describe('viewAny', function () {
    test('admin can view any rates', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('ville role user can view any rates', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('cpas role user can view any rates', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('user without role cannot view any rates', function () {
        $user = User::factory()->create();

        expect($this->policy->viewAny($user))->toBeFalse();
    });
});

describe('view', function () {
    test('any user can view a rate', function () {
        $user = User::factory()->create();
        $rate = Rate::factory()->create();

        expect($this->policy->view($user, $rate))->toBeTrue();
    });

    test('user with role can view a rate', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->view($user, $rate))->toBeTrue();
    });
});

describe('create', function () {
    test('admin can create rates', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeTrue();
    });

    test('ville role user cannot create rates', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeFalse();
    });

    test('cpas role user cannot create rates', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeFalse();
    });

    test('user without role cannot create rates', function () {
        $user = User::factory()->create();

        expect($this->policy->create($user))->toBeFalse();
    });
});

describe('update', function () {
    test('admin can update rates', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->update($user, $rate))->toBeTrue();
    });

    test('ville role user cannot update rates', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->update($user, $rate))->toBeFalse();
    });

    test('cpas role user cannot update rates', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->update($user, $rate))->toBeFalse();
    });

    test('user without role cannot update rates', function () {
        $user = User::factory()->create();
        $rate = Rate::factory()->create();

        expect($this->policy->update($user, $rate))->toBeFalse();
    });
});

describe('delete', function () {
    test('admin can delete rates', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->delete($rate, $rate))->toBeTrue();
    });

    test('ville role user cannot delete rates', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->delete($rate, $rate))->toBeFalse();
    });

    test('cpas role user cannot delete rates', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->delete($rate, $rate))->toBeFalse();
    });

    test('user without role cannot delete rates', function () {
        $user = User::factory()->create();
        $rate = Rate::factory()->create();

        expect($this->policy->delete($rate, $rate))->toBeFalse();
    });
});

describe('restore', function () {
    test('restore always returns false', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->restore($rate, $rate))->toBeFalse();
    });
});

describe('forceDelete', function () {
    test('forceDelete always returns false', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->forceDelete($rate, $rate))->toBeFalse();
    });
});
