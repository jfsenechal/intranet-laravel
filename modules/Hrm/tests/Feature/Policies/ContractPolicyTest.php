<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\RolesEnum;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Direction;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Policies\ContractPolicy;
use AcMarche\Hrm\Policies\EmployeePolicy;
use AcMarche\Security\Models\Role;
use App\Models\User;

beforeEach(function (): void {
    $this->policy = new ContractPolicy;

    $this->directionHead = function (string $username = 'director1'): User {
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_GRH_DIRECTION->value]);
        $user = User::factory()->create(['is_administrator' => false, 'username' => $username]);
        $user->roles()->attach($role);

        return $user;
    };
});

describe('direction head authorization', function (): void {
    it('grants view on a contract of their own direction', function (): void {
        $director = ($this->directionHead)();
        $direction = Direction::factory()->create(['director' => 'director1']);
        $contract = Contract::factory()->create(['direction_id' => $direction->id]);

        expect($this->policy->view($director, $contract))->toBeTrue();
    });

    it('grants view on a contract of another direction when the employee is visible to them', function (): void {
        $director = ($this->directionHead)();
        $direction = Direction::factory()->create(['director' => 'director1']);
        $otherDirection = Direction::factory()->create(['director' => 'someone-else']);

        $employee = Employee::factory()->create();
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'direction_id' => $direction->id,
            'is_closed' => false,
            'is_suspended' => false,
            'end_date' => null,
        ]);
        $otherContract = Contract::factory()->create([
            'employee_id' => $employee->id,
            'direction_id' => $otherDirection->id,
        ]);

        expect((new EmployeePolicy)->view($director, $employee))->toBeTrue()
            ->and($this->policy->view($director, $otherContract))->toBeTrue();
    });

    it('denies view when neither the direction nor the employee is theirs', function (): void {
        $director = ($this->directionHead)();
        Direction::factory()->create(['director' => 'director1']);
        $otherDirection = Direction::factory()->create(['director' => 'someone-else']);

        $employee = Employee::factory()->create();
        $contract = Contract::factory()->create([
            'employee_id' => $employee->id,
            'direction_id' => $otherDirection->id,
            'is_closed' => false,
            'is_suspended' => false,
            'end_date' => null,
        ]);

        expect((new EmployeePolicy)->view($director, $employee))->toBeFalse()
            ->and($this->policy->view($director, $contract))->toBeFalse();
    });

    it('keeps the write abilities restricted to administrators', function (): void {
        $director = ($this->directionHead)();

        expect($this->policy->create($director))->toBeFalse()
            ->and($this->policy->update($director))->toBeFalse()
            ->and($this->policy->delete($director))->toBeFalse()
            ->and($this->policy->replicate($director))->toBeFalse();
    });

    it('grants replicate to a ROLE_GRH_ADMIN user only', function (): void {
        $director = ($this->directionHead)();
        $grhAdmin = User::factory()->create(['is_administrator' => false]);
        $grhAdmin->roles()->attach(Role::factory()->create(['name' => RolesEnum::ROLE_GRH_ADMIN->value]));

        expect($this->policy->replicate($grhAdmin))->toBeTrue()
            ->and($this->policy->replicate($director))->toBeFalse()
            ->and($this->policy->replicate(User::factory()->create(['is_administrator' => false])))->toBeFalse();
    });

    it('grants viewAny so the record pages stay reachable', function (): void {
        $director = ($this->directionHead)();
        $agent = User::factory()->create(['is_administrator' => false]);

        expect($this->policy->viewAny($director))->toBeTrue()
            ->and($this->policy->viewAny($agent))->toBeFalse();
    });
});

describe('scopeVisibleTo', function (): void {
    it('keeps a direction head to the contracts of their own direction', function (): void {
        $director = ($this->directionHead)();
        $direction = Direction::factory()->create(['director' => 'director1']);
        $otherDirection = Direction::factory()->create(['director' => 'someone-else']);

        $own = Contract::factory()->create(['direction_id' => $direction->id]);
        $foreign = Contract::factory()->create(['direction_id' => $otherDirection->id]);

        $visibleIds = $this->policy->scopeVisibleTo(Contract::query(), $director)->pluck('id');

        expect($visibleIds)->toContain($own->id)
            ->and($visibleIds)->not->toContain($foreign->id);
    });

    it('includes the other contracts of an employee the direction head can view', function (): void {
        $director = ($this->directionHead)();
        $direction = Direction::factory()->create(['director' => 'director1']);
        $otherDirection = Direction::factory()->create(['director' => 'someone-else']);

        $employee = Employee::factory()->create();
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'direction_id' => $direction->id,
            'is_closed' => false,
            'is_suspended' => false,
            'end_date' => null,
        ]);
        $otherContract = Contract::factory()->create([
            'employee_id' => $employee->id,
            'direction_id' => $otherDirection->id,
        ]);

        $visibleIds = $this->policy->scopeVisibleTo(Contract::query(), $director)->pluck('id');

        expect($visibleIds)->toContain($otherContract->id);
    });

    it('returns every contract to an administrator', function (): void {
        $admin = User::factory()->create(['is_administrator' => true]);
        Contract::factory()->count(3)->create();

        expect($this->policy->scopeVisibleTo(Contract::query(), $admin)->count())->toBe(3);
    });

    it('returns no contract to a user without an HRM role', function (): void {
        $agent = User::factory()->create(['is_administrator' => false]);
        Contract::factory()->count(3)->create();

        expect($this->policy->scopeVisibleTo(Contract::query(), $agent)->count())->toBe(0);
    });
});
