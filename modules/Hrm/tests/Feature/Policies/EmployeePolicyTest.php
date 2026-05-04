<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\RolesEnum;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Policies\EmployeePolicy;
use AcMarche\Security\Models\Role;
use App\Models\User;

beforeEach(function (): void {
    $this->policy = new EmployeePolicy;
});

describe('admin authorization', function (): void {
    it('grants administrators full create/update/delete access', function (): void {
        $admin = User::factory()->create(['is_administrator' => true]);
        $employee = Employee::factory()->create();

        expect($this->policy->view($admin, $employee))->toBeTrue()
            ->and($this->policy->create($admin))->toBeTrue()
            ->and($this->policy->update($admin))->toBeTrue()
            ->and($this->policy->delete($admin))->toBeTrue()
            ->and($this->policy->restore($admin))->toBeTrue();
    });

    it('grants viewAny only when user has an HRM role', function (): void {
        $adminWithoutRole = User::factory()->create(['is_administrator' => true]);
        $hrmAdminRole = Role::factory()->create(['name' => RolesEnum::ROLE_GRH_ADMIN->value]);
        $adminWithRole = User::factory()->create(['is_administrator' => true]);
        $adminWithRole->roles()->attach($hrmAdminRole);

        expect($this->policy->viewAny($adminWithoutRole))->toBeFalse()
            ->and($this->policy->viewAny($adminWithRole))->toBeTrue();
    });

    it('always denies forceDelete', function (): void {
        expect($this->policy->forceDelete())->toBeFalse();
    });
});

describe('non-admin authorization', function (): void {
    it('denies create/update/delete to non-administrators without GRH role', function (): void {
        $user = User::factory()->create(['is_administrator' => false]);

        expect($this->policy->create($user))->toBeFalse()
            ->and($this->policy->update($user))->toBeFalse()
            ->and($this->policy->delete($user))->toBeFalse()
            ->and($this->policy->restore($user))->toBeFalse();
    });

    it('denies viewAny when user has no HRM role', function (): void {
        $user = User::factory()->create(['is_administrator' => false]);

        expect($this->policy->viewAny($user))->toBeFalse();
    });

    it('grants viewAny to user with ROLE_GRH_CPAS_READ role', function (): void {
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_GRH_CPAS_READ->value]);
        $user = User::factory()->create(['is_administrator' => false]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });

    it('grants viewAny to user with ROLE_GRH_VILLE_READ role', function (): void {
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_GRH_VILLE_READ->value]);
        $user = User::factory()->create(['is_administrator' => false]);
        $user->roles()->attach($role);

        expect($this->policy->viewAny($user))->toBeTrue();
    });
});
