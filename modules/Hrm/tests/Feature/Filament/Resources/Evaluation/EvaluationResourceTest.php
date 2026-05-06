<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\EvaluationResultEnum;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\EvaluationsRelationManager;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Evaluation;
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
    it('can render the EvaluationsRelationManager', function (): void {
        Evaluation::factory(3)->create([
            'employee_id' => $this->employee->id,
        ]);

        Livewire::test(EvaluationsRelationManager::class, [
            'ownerRecord' => $this->employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->assertOk();
    });

    it('can list evaluations for an employee', function (): void {
        $evaluations = Evaluation::factory(3)->create([
            'employee_id' => $this->employee->id,
        ]);

        Livewire::test(EvaluationsRelationManager::class, [
            'ownerRecord' => $this->employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->loadTable()
            ->assertCanSeeTableRecords($evaluations);
    });

    it('does not show evaluations from other employees', function (): void {
        $otherEmployee = Employee::factory()->create();
        $otherEvaluations = Evaluation::factory(2)->create([
            'employee_id' => $otherEmployee->id,
        ]);

        Livewire::test(EvaluationsRelationManager::class, [
            'ownerRecord' => $this->employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->loadTable()
            ->assertCanNotSeeTableRecords($otherEvaluations);
    });
});

describe('model behavior', function (): void {
    it('casts result to EvaluationResultEnum', function (): void {
        $evaluation = Evaluation::factory()->create([
            'result' => EvaluationResultEnum::POSITIVE->value,
        ]);

        expect($evaluation->result)->toBe(EvaluationResultEnum::POSITIVE);
    });

    it('casts evaluation_date to date', function (): void {
        $evaluation = Evaluation::factory()->create();

        expect($evaluation->evaluation_date)->toBeInstanceOf(Carbon\CarbonInterface::class);
    });
});
