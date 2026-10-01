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

    it('keeps viewAny and the write abilities restricted to administrators', function (): void {
        $director = ($this->directionHead)();

        expect($this->policy->viewAny($director))->toBeFalse()
            ->and($this->policy->create($director))->toBeFalse()
            ->and($this->policy->update($director))->toBeFalse()
            ->and($this->policy->delete($director))->toBeFalse();
    });
});
