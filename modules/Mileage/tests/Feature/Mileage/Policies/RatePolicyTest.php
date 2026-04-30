<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Policies\RatePolicy;
use AcMarche\Security\Models\Role;
use App\Models\User;

beforeEach(function (): void {
    $this->policy = new RatePolicy();
});

describe('viewAny', function (): void {
    test('admin can view any rates', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('ville role user can view any rates', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('cpas role user can view any rates', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('user without role cannot view any rates', function (): void {
        $user = User::factory()->create();

        expect($this->policy->viewAny($user))->toBeFalse();
    });
});

describe('view', function (): void {
    test('any user can view a rate', function (): void {
        $user = User::factory()->create();
        $rate = Rate::factory()->create();

        expect($this->policy->view($user, $rate))->toBeTrue();
    });

    test('user with role can view a rate', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->view($user, $rate))->toBeTrue();
    });
});

describe('create', function (): void {
    test('admin can create rates', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeTrue();
    });

    test('ville role user cannot create rates', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeFalse();
    });

    test('cpas role user cannot create rates', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeFalse();
    });

    test('user without role cannot create rates', function (): void {
        $user = User::factory()->create();

        expect($this->policy->create($user))->toBeFalse();
    });
});

describe('update', function (): void {
    test('admin can update rates', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->update($user, $rate))->toBeTrue();
    });

    test('ville role user cannot update rates', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->update($user, $rate))->toBeFalse();
    });

    test('cpas role user cannot update rates', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->update($user, $rate))->toBeFalse();
    });

    test('user without role cannot update rates', function (): void {
        $user = User::factory()->create();
        $rate = Rate::factory()->create();

        expect($this->policy->update($user, $rate))->toBeFalse();
    });
});

describe('delete', function (): void {
    test('admin can delete rates', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->delete($user, $rate))->toBeTrue();
    });

    test('ville role user cannot delete rates', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->delete($user, $rate))->toBeFalse();
    });

    test('cpas role user cannot delete rates', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->delete($user, $rate))->toBeFalse();
    });

    test('user without role cannot delete rates', function (): void {
        $user = User::factory()->create();
        $rate = Rate::factory()->create();

        expect($this->policy->delete($user, $rate))->toBeFalse();
    });
});

describe('restore', function (): void {
    test('restore always returns false', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->restore($user, $rate))->toBeFalse();
    });
});

describe('forceDelete', function (): void {
    test('forceDelete always returns false', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $rate = Rate::factory()->create();

        expect($this->policy->forceDelete($user, $rate))->toBeFalse();
    });
});
