<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\EvaluationResultEnum;
use AcMarche\Hrm\Filament\Resources\Employees\EmployeeResource;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\EvaluationsRelationManager;
use AcMarche\Hrm\Filament\Resources\Evaluations\EvaluationResource;
use AcMarche\Hrm\Filament\Resources\Evaluations\Pages\ViewEvaluation;
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

describe('view page', function (): void {
    it('can render the view page', function (): void {
        $evaluation = Evaluation::factory()->create([
            'employee_id' => $this->employee->id,
        ]);

        Livewire::test(ViewEvaluation::class, [
            'record' => $evaluation->id,
        ])
            ->assertOk();
    });

    it('titles the page with the employee of the evaluation', function (): void {
        $evaluation = Evaluation::factory()->create([
            'employee_id' => $this->employee->id,
        ]);

        Livewire::test(ViewEvaluation::class, [
            'record' => $evaluation->id,
        ])
            ->assertSee('Évaluation de '.$this->employee->full_name);
    });

    it('links back to the employee', function (): void {
        $evaluation = Evaluation::factory()->create([
            'employee_id' => $this->employee->id,
        ]);

        Livewire::test(ViewEvaluation::class, [
            'record' => $evaluation->id,
        ])
            ->assertSee(EmployeeResource::getUrl('view', ['record' => $this->employee->id]));
    });

    it('is reachable by url', function (): void {
        $evaluation = Evaluation::factory()->create([
            'employee_id' => $this->employee->id,
        ]);

        $this->get(ViewEvaluation::getUrl(['record' => $evaluation]))
            ->assertOk();
    });

    it('keeps the resource out of the navigation', function (): void {
        expect(EvaluationResource::shouldRegisterNavigation())->toBeFalse();
    });

    it('falls back to the employees list for routing without an index page', function (): void {
        expect(EvaluationResource::getIndexUrl())->toBe(EmployeeResource::getUrl('index'));
    });

    it('forbids an evaluation of an employee the user cannot view', function (): void {
        $evaluation = Evaluation::factory()->create([
            'employee_id' => $this->employee->id,
        ]);

        $this->actingAs(User::factory()->create());

        $this->get(ViewEvaluation::getUrl(['record' => $evaluation]))
            ->assertForbidden();
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
