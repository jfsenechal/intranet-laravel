<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\ApplicationsRelationManager;
use AcMarche\Hrm\Models\Application;
use AcMarche\Hrm\Models\Employee;
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
    it('can render the ApplicationsRelationManager', function (): void {
        Application::factory(3)->create([
            'employee_id' => $this->employee->id,
        ]);

        Livewire::test(ApplicationsRelationManager::class, [
            'ownerRecord' => $this->employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->assertOk();
    });

    it('can list applications for an employee', function (): void {
        $applications = Application::factory(3)->create([
            'employee_id' => $this->employee->id,
        ]);

        Livewire::test(ApplicationsRelationManager::class, [
            'ownerRecord' => $this->employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->loadTable()
            ->assertCanSeeTableRecords($applications);
    });

    it('does not show applications from other employees', function (): void {
        $otherEmployee = Employee::factory()->create();
        $otherApplications = Application::factory(2)->create([
            'employee_id' => $otherEmployee->id,
        ]);

        Livewire::test(ApplicationsRelationManager::class, [
            'ownerRecord' => $this->employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->loadTable()
            ->assertCanNotSeeTableRecords($otherApplications);
    });
});

describe('model behavior', function (): void {
    it('casts boolean fields correctly', function (): void {
        $application = Application::factory()->create([
            'is_spontaneous' => true,
            'is_public_call' => false,
            'is_priority' => true,
        ]);

        expect($application->is_spontaneous)->toBeTrue();
        expect($application->is_public_call)->toBeFalse();
        expect($application->is_priority)->toBeTrue();
    });

    it('casts received_at to date', function (): void {
        $application = Application::factory()->create();

        expect($application->received_at)->toBeInstanceOf(Carbon\CarbonInterface::class);
    });
});
