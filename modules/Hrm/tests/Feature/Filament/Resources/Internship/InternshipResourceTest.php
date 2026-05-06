<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\InternshipsRelationManager;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Internship;
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
    it('can render the InternshipsRelationManager', function (): void {
        Internship::factory(3)->create([
            'employee_id' => $this->employee->id,
        ]);

        Livewire::test(InternshipsRelationManager::class, [
            'ownerRecord' => $this->employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->assertOk();
    });

    it('can list internships for an employee', function (): void {
        $internships = Internship::factory(3)->create([
            'employee_id' => $this->employee->id,
        ]);

        Livewire::test(InternshipsRelationManager::class, [
            'ownerRecord' => $this->employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->loadTable()
            ->assertCanSeeTableRecords($internships);
    });

    it('does not show internships from other employees', function (): void {
        $otherEmployee = Employee::factory()->create();
        $otherInternships = Internship::factory(2)->create([
            'employee_id' => $otherEmployee->id,
        ]);

        Livewire::test(InternshipsRelationManager::class, [
            'ownerRecord' => $this->employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->loadTable()
            ->assertCanNotSeeTableRecords($otherInternships);
    });
});

describe('model behavior', function (): void {
    it('casts dates correctly', function (): void {
        $internship = Internship::factory()->create();

        expect($internship->start_date)->toBeInstanceOf(Carbon\CarbonInterface::class);
        expect($internship->end_date)->toBeInstanceOf(Carbon\CarbonInterface::class);
    });

    it('belongs to an employee', function (): void {
        $internship = Internship::factory()->create([
            'employee_id' => $this->employee->id,
        ]);

        expect($internship->employee->id)->toBe($this->employee->id);
    });
});
