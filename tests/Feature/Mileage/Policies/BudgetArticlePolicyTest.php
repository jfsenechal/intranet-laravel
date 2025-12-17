<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Models\BudgetArticle;
use AcMarche\Mileage\Policies\BudgetArticlePolicy;
use AcMarche\Security\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->policy = new BudgetArticlePolicy();
});

describe('viewAny', function () {
    test('admin can view any budget articles', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);
        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('ville role user can view any budget articles', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('cpas role user can view any budget articles', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    test('user without role cannot view any budget articles', function () {
        $user = User::factory()->create();

        expect($this->policy->viewAny($user))->toBeFalse();
    });
});

describe('view', function () {
    test('any user can view a budget article', function () {
        $user = User::factory()->create();
        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->view($user, $budgetArticle))->toBeTrue();
    });

    test('user with role can view a budget article', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->view($user, $budgetArticle))->toBeTrue();
    });
});

describe('create', function () {
    test('admin can create budget articles', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeTrue();
    });

    test('ville role user cannot create budget articles', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeFalse();
    });

    test('cpas role user cannot create budget articles', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        expect($this->policy->create($user))->toBeFalse();
    });

    test('user without role cannot create budget articles', function () {
        $user = User::factory()->create();

        expect($this->policy->create($user))->toBeFalse();
    });
});

describe('update', function () {
    test('admin can update budget articles', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->update($user, $budgetArticle))->toBeTrue();
    });

    test('ville role user cannot update budget articles', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->update($user, $budgetArticle))->toBeFalse();
    });

    test('cpas role user cannot update budget articles', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->update($user, $budgetArticle))->toBeFalse();
    });

    test('user without role cannot update budget articles', function () {
        $user = User::factory()->create();
        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->update($user, $budgetArticle))->toBeFalse();
    });
});

describe('delete', function () {
    test('admin can delete budget articles', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->delete($user, $budgetArticle))->toBeTrue();
    });

    test('ville role user cannot delete budget articles', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $user->roles()->attach($role);

        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->delete($user, $budgetArticle))->toBeFalse();
    });

    test('cpas role user cannot delete budget articles', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $user->roles()->attach($role);

        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->delete($user, $budgetArticle))->toBeFalse();
    });

    test('user without role cannot delete budget articles', function () {
        $user = User::factory()->create();
        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->delete($user, $budgetArticle))->toBeFalse();
    });
});

describe('restore', function () {
    test('restore always returns false', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->restore($user, $budgetArticle))->toBeFalse();
    });
});

describe('forceDelete', function () {
    test('forceDelete always returns false', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $user->roles()->attach($role);

        $budgetArticle = BudgetArticle::factory()->create();

        expect($this->policy->forceDelete($budgetArticle, $budgetArticle))->toBeFalse();
    });
});
