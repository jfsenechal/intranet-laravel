<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\EvaluationResultEnum;
use AcMarche\Hrm\Enums\RolesEnum;
use AcMarche\Hrm\Filament\Resources\Employees\EmployeeResource;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\EvaluationsRelationManager;
use AcMarche\Hrm\Filament\Resources\Evaluations\EvaluationResource;
use AcMarche\Hrm\Filament\Resources\Evaluations\Pages\ViewEvaluation;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Direction;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Evaluation;
use AcMarche\Security\Models\Role;
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

    it('opens an evaluation on its view page', function (): void {
        $evaluation = Evaluation::factory()->create([
            'employee_id' => $this->employee->id,
        ]);

        Livewire::test(EvaluationsRelationManager::class, [
            'ownerRecord' => $this->employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->loadTable()
            ->assertSee(ViewEvaluation::getUrl(['record' => $evaluation], panel: 'hrm-panel'));
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

    it('hides an evaluation of an employee the user cannot view', function (): void {
        $evaluation = Evaluation::factory()->create([
            'employee_id' => $this->employee->id,
        ]);

        $this->actingAs(User::factory()->create());

        // The scoped resource query never resolves the record, so its existence
        // is not disclosed by a 403.
        $this->get(ViewEvaluation::getUrl(['record' => $evaluation]))
            ->assertNotFound();
    });

    it('opens an evaluation of one of their agents to a direction head', function (): void {
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_GRH_DIRECTION->value]);
        $director = User::factory()->create(['is_administrator' => false, 'username' => 'director1']);
        $director->roles()->attach($role);
        $direction = Direction::factory()->create(['director' => 'director1']);
        Contract::factory()->create([
            'employee_id' => $this->employee->id,
            'direction_id' => $direction->id,
            'is_closed' => false,
            'is_suspended' => false,
            'end_date' => null,
        ]);
        $evaluation = Evaluation::factory()->create([
            'employee_id' => $this->employee->id,
        ]);
        $foreignEvaluation = Evaluation::factory()->create([
            'employee_id' => Employee::factory()->create()->id,
        ]);

        $this->actingAs($director);

        $this->get(ViewEvaluation::getUrl(['record' => $evaluation]))
            ->assertOk();
        $this->get(ViewEvaluation::getUrl(['record' => $foreignEvaluation]))
            ->assertNotFound();
    });

    it('deletes the evaluation and returns to the employee', function (): void {
        $evaluation = Evaluation::factory()->create([
            'employee_id' => $this->employee->id,
        ]);

        Livewire::test(ViewEvaluation::class, [
            'record' => $evaluation->id,
        ])
            ->callAction('delete')
            ->assertRedirect(EmployeeResource::getUrl('view', ['record' => $this->employee->id]));

        expect(Evaluation::query()->find($evaluation->id))->toBeNull();
    });

    it('replicates the evaluation to another employee without its documents', function (): void {
        $evaluation = Evaluation::factory()->create([
            'employee_id' => $this->employee->id,
            'result' => EvaluationResultEnum::POSITIVE->value,
            'file1_name' => 'evaluations/original.pdf',
        ]);
        $targetEmployee = Employee::factory()->create();

        Livewire::test(ViewEvaluation::class, [
            'record' => $evaluation->id,
        ])
            ->callAction('replicate', ['employee_id' => $targetEmployee->id])
            ->assertHasNoActionErrors();

        $replica = Evaluation::query()->where('employee_id', $targetEmployee->id)->sole();

        expect($replica->result)->toBe(EvaluationResultEnum::POSITIVE)
            ->and($replica->file1_name)->toBeNull();
    });

    it('only lets administrators replicate or delete an evaluation', function (): void {
        $evaluation = Evaluation::factory()->create([
            'employee_id' => $this->employee->id,
        ]);

        expect(auth()->user()->can('replicate', $evaluation))->toBeTrue();

        $this->actingAs(User::factory()->create());

        expect(auth()->user()->can('replicate', $evaluation))->toBeFalse()
            ->and(auth()->user()->can('delete', $evaluation))->toBeFalse();
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
