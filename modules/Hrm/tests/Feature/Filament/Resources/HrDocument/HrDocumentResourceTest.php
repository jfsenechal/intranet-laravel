<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\DocumentsRelationManager;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\HrDocument;
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
    it('can render the DocumentsRelationManager', function (): void {
        HrDocument::factory(3)->create([
            'employee_id' => $this->employee->id,
        ]);

        Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $this->employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->assertOk();
    });

    it('can list documents for an employee', function (): void {
        $documents = HrDocument::factory(3)->create([
            'employee_id' => $this->employee->id,
        ]);

        Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $this->employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->loadTable()
            ->assertCanSeeTableRecords($documents);
    });

    it('does not show documents from other employees', function (): void {
        $otherEmployee = Employee::factory()->create();
        $otherDocuments = HrDocument::factory(2)->create([
            'employee_id' => $otherEmployee->id,
        ]);

        Livewire::test(DocumentsRelationManager::class, [
            'ownerRecord' => $this->employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->loadTable()
            ->assertCanNotSeeTableRecords($otherDocuments);
    });
});

describe('model behavior', function (): void {
    it('belongs to an employee', function (): void {
        $document = HrDocument::factory()->create([
            'employee_id' => $this->employee->id,
        ]);

        expect($document->employee->id)->toBe($this->employee->id);
    });
});
