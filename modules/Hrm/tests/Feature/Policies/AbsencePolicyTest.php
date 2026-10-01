<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\RolesEnum;
use AcMarche\Hrm\Models\Absence;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Direction;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Policies\AbsencePolicy;
use AcMarche\Security\Models\Role;
use App\Models\User;

beforeEach(function (): void {
    $this->policy = new AbsencePolicy;

    $this->directionHead = function (): User {
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_GRH_DIRECTION->value]);
        $user = User::factory()->create(['is_administrator' => false, 'username' => 'director1']);
        $user->roles()->attach($role);

        return $user;
    };

    $this->agentOfDirection = function (Direction $direction): Employee {
        $employee = Employee::factory()->create();
        Contract::factory()->create([
            'employee_id' => $employee->id,
            'direction_id' => $direction->id,
            'is_closed' => false,
            'is_suspended' => false,
            'end_date' => null,
        ]);

        return $employee;
    };
});

describe('direction head authorization', function (): void {
    it('grants view on an absence of one of their own agents', function (): void {
        $director = ($this->directionHead)();
        $direction = Direction::factory()->create(['director' => 'director1']);
        $absence = Absence::factory()->create([
            'employee_id' => ($this->agentOfDirection)($direction)->id,
        ]);

        expect($this->policy->view($director, $absence))->toBeTrue();
    });

    it('denies view on an absence of an agent outside their direction', function (): void {
        $director = ($this->directionHead)();
        Direction::factory()->create(['director' => 'director1']);
        $absence = Absence::factory()->create([
            'employee_id' => Employee::factory()->create()->id,
        ]);

        expect($this->policy->view($director, $absence))->toBeFalse();
    });

    it('grants viewAny so the record pages stay reachable', function (): void {
        $director = ($this->directionHead)();
        $agent = User::factory()->create(['is_administrator' => false]);

        expect($this->policy->viewAny($director))->toBeTrue()
            ->and($this->policy->viewAny($agent))->toBeFalse();
    });

    it('keeps the write abilities restricted to administrators', function (): void {
        $director = ($this->directionHead)();

        expect($this->policy->create($director))->toBeFalse()
            ->and($this->policy->update($director))->toBeFalse()
            ->and($this->policy->delete($director))->toBeFalse();
    });

    it('always denies forceDelete', function (): void {
        expect($this->policy->forceDelete())->toBeFalse();
    });
});

describe('scopeVisibleTo', function (): void {
    it('keeps a direction head to the absences of their own agents', function (): void {
        $director = ($this->directionHead)();
        $direction = Direction::factory()->create(['director' => 'director1']);

        $own = Absence::factory()->create([
            'employee_id' => ($this->agentOfDirection)($direction)->id,
        ]);
        $foreign = Absence::factory()->create([
            'employee_id' => Employee::factory()->create()->id,
        ]);

        $visibleIds = $this->policy->scopeVisibleTo(Absence::query(), $director)->pluck('id');

        expect($visibleIds)->toContain($own->id)
            ->and($visibleIds)->not->toContain($foreign->id);
    });

    it('returns every absence to an administrator', function (): void {
        $admin = User::factory()->create(['is_administrator' => true]);
        Absence::factory()->count(3)->create();

        expect($this->policy->scopeVisibleTo(Absence::query(), $admin)->count())->toBe(3);
    });

    it('returns no absence to a user without an HRM role', function (): void {
        $agent = User::factory()->create(['is_administrator' => false]);
        Absence::factory()->count(3)->create();

        expect($this->policy->scopeVisibleTo(Absence::query(), $agent)->count())->toBe(0);
    });
});
