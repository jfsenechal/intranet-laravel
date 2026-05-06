<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\ValorizationsRelationManager;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Valorization;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
    $this->adminUser = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($this->adminUser);
    $this->employee = Employee::factory()->create();
});

describe('relation manager', function (): void {
    it('can render the ValorizationsRelationManager', function (): void {
        Valorization::factory(3)->create([
            'employee_id' => $this->employee->id,
        ]);

        Livewire::test(ValorizationsRelationManager::class, [
            'ownerRecord' => $this->employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->assertOk();
    });

    it('can list valorizations for an employee', function (): void {
        $valorizations = Valorization::factory(3)->create([
            'employee_id' => $this->employee->id,
        ]);

        Livewire::test(ValorizationsRelationManager::class, [
            'ownerRecord' => $this->employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->loadTable()
            ->assertCanSeeTableRecords($valorizations);
    });

    it('does not show valorizations from other employees', function (): void {
        $otherEmployee = Employee::factory()->create();
        $otherValorizations = Valorization::factory(2)->create([
            'employee_id' => $otherEmployee->id,
        ]);

        Livewire::test(ValorizationsRelationManager::class, [
            'ownerRecord' => $this->employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->loadTable()
            ->assertCanNotSeeTableRecords($otherValorizations);
    });
});

describe('model behavior', function (): void {
    it('belongs to an employee', function (): void {
        $valorization = Valorization::factory()->create([
            'employee_id' => $this->employee->id,
        ]);

        expect($valorization->employee->id)->toBe($this->employee->id);
    });
});
