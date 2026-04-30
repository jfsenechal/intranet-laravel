<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Models\BudgetArticle;
use AcMarche\Mileage\Policies\UserPolicy;
use AcMarche\Security\Models\Role;
use App\Models\User;

beforeEach(function (): void {
    $this->policy = new UserPolicy();
});

describe('viewAny', function (): void {
    test('admin can view any users', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('ville role user cannot view users', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeFalse();
    });

    test('cpas role user cannot view users', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeFalse();
    });

    test('user without role cannot view users', function (): void {
        $user = User::factory()->create();

        expect($this->policy->viewAny($user))->toBeFalse();
    });
});

describe('view', function (): void {
    test('admin can view a user', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->view($user, $budgetArticle))->toBeTrue();
    });

    test('ville role user cannot view a user', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->view($user, $budgetArticle))->toBeFalse();
    });

    test('user without role cannot view a user', function (): void {
        $user = User::factory()->create();
        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->view($user, $budgetArticle))->toBeFalse();
    });
});

describe('create', function (): void {
    test('admin can create users', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeTrue();
    });

    test('ville role user cannot create users', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeFalse();
    });

    test('cpas role user cannot create users', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeFalse();
    });

    test('user without role cannot create users', function (): void {
        $user = User::factory()->create();

        expect($this->policy->create($user))->toBeFalse();
    });
});

describe('update', function (): void {
    test('admin can update users', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->update($user, $budgetArticle))->toBeTrue();
    });

    test('ville role user cannot update users', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->update($user, $budgetArticle))->toBeFalse();
    });

    test('user without role cannot update users', function (): void {
        $user = User::factory()->create();
        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->update($user, $budgetArticle))->toBeFalse();
    });
});

describe('delete', function (): void {
    test('admin can delete users', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->delete($user, $budgetArticle))->toBeTrue();
    });

    test('ville role user cannot delete users', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->delete($user, $budgetArticle))->toBeFalse();
    });

    test('user without role cannot delete users', function (): void {
        $user = User::factory()->create();
        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->delete($user, $budgetArticle))->toBeFalse();
    });
});

describe('restore', function (): void {
    test('restore always returns false', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->restore($user, $budgetArticle))->toBeFalse();
    });
});

describe('forceDelete', function (): void {
    test('forceDelete always returns false', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->forceDelete($budgetArticle, $budgetArticle))->toBeFalse();
    });
});
