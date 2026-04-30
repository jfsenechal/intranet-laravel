<?php

declare(strict_types=1);

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Pst\Enums\RoleEnum;
use AcMarche\Pst\Filament\Resources\Service\Pages\CreateService;
use AcMarche\Pst\Filament\Resources\Service\Pages\EditService;
use AcMarche\Pst\Filament\Resources\Service\Pages\ListServices;
use AcMarche\Pst\Filament\Resources\Service\Pages\ViewService;
use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\OperationalObjective;
use AcMarche\Pst\Models\Service;
use AcMarche\Pst\Models\StrategicObjective;
use AcMarche\Security\Models\Role;
use AcMarche\Security\Repository\UserRepository;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('pst'));
    $adminRole = Role::factory()->create(['name' => RoleEnum::ADMIN->value]);
    $this->adminUser = User::factory()->create();
    $this->adminUser->roles()->attach($adminRole);

    $this->actingAs($this->adminUser);
});

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListServices::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateService::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = Service::factory()->create();

        Livewire::test(ViewService::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Service::factory()->create();

        Livewire::test(EditService::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('table columns', function (): void {
    it('has column', function (string $column): void {
        Livewire::test(ListServices::class)
            ->assertTableColumnExists($column);
    })->with(['name', 'initials', 'users_count']);

    it('can render column', function (string $column): void {
        Service::factory()->create();

        Livewire::test(ListServices::class)
            ->loadTable()
            ->assertCanRenderTableColumn($column);
    })->with(['name']);

    it('can render toggleable column hidden by default', function (string $column): void {
        Service::factory()->create();

        Livewire::test(ListServices::class)
            ->loadTable()
            ->toggleAllTableColumns()
            ->assertCanRenderTableColumn($column);
    })->with(['users_count']);

    it('can sort by name', function (): void {
        $records = Service::factory(3)->create();

        Livewire::test(ListServices::class)
            ->loadTable()
            ->sortTable('name')
            ->assertCanSeeTableRecords($records->sortBy('name'), inOrder: true);
    });

    it('can search by name', function (): void {
        $records = Service::factory(3)->create();
        $searchRecord = $records->first();

        Livewire::test(ListServices::class)
            ->loadTable()
            ->searchTable($searchRecord->name)
            ->assertCanSeeTableRecords($records->where('name', $searchRecord->name));
    });
});

describe('crud operations', function (): void {
    it('can create a service', function (): void {
        $newData = Service::factory()->make();

        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => $newData->name,
                'initials' => 'TST',
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseHas(Service::class, [
            'name' => $newData->name,
            'initials' => 'TST',
        ]);
    });

    it('can create a service with users', function (): void {
        $newData = Service::factory()->make();

        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();

        $service = Service::where('name', $newData->name)->first();
        expect($service)->not->toBeNull();
    });

    it('can update a service', function (): void {
        $record = Service::factory()->create();
        $newData = Service::factory()->make();

        Livewire::test(EditService::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified();

        assertDatabaseHas(Service::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });

    it('can delete a service', function (): void {
        $record = Service::factory()->create();

        Livewire::test(ViewService::class, [
            'record' => $record->id,
        ])
            ->callAction(DeleteAction::class)
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseMissing($record);
    });

    it('can bulk delete services', function (): void {
        $records = Service::factory(3)->create();

        Livewire::test(ListServices::class)
            ->loadTable()
            ->assertCanSeeTableRecords($records)
            ->selectTableRecords($records)
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified()
            ->assertCanNotSeeTableRecords($records);

        $records->each(fn (Service $record) => assertDatabaseMissing($record));
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        $newData = Service::factory()->make();

        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => $newData->name,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
        '`initials` is max 30 characters' => [['initials' => Str::random(31)], ['initials' => 'max']],
    ]);

    it('validates the form data on edit', function (array $data, array $errors): void {
        $record = Service::factory()->create();

        Livewire::test(EditService::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $record->name,
                ...$data,
            ])
            ->call('save')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    ]);
});

describe('form fields', function (): void {
    it('has name field', function (): void {
        Livewire::test(CreateService::class)
            ->assertFormFieldExists('name');
    });

    it('has initials field', function (): void {
        Livewire::test(CreateService::class)
            ->assertFormFieldExists('initials');
    });

    it('has users field', function (): void {
        Livewire::test(CreateService::class)
            ->assertFormFieldExists('users');
    });
});

describe('department-filtered action counts', function (): void {
    it('counts only actions matching selected department', function (): void {
        $service = Service::factory()->create();
        $strategicObjective = StrategicObjective::factory()->create();
        $operationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $strategicObjective->id,
        ]);

        // Set selected department to VILLE
        session([UserRepository::$department_selected_key => DepartmentEnum::VILLE->value]);

        // Action in VILLE department (should be counted)
        $villeAction = Action::factory()->create([
            'department' => DepartmentEnum::VILLE->value,
            'operational_objective_id' => $operationalObjective->id,
        ]);
        $service->leadingActions()->attach($villeAction);

        // Action in CPAS department (should NOT be counted)
        $cpasAction = Action::factory()->create([
            'department' => DepartmentEnum::CPAS->value,
            'operational_objective_id' => $operationalObjective->id,
        ]);
        $service->leadingActions()->attach($cpasAction);

        // Should count 1: only villeAction
        expect($service->leadingActionsForDepartment()->count())->toBe(1);
    });

    it('filters leading and partnering actions by selected department', function (): void {
        $service = Service::factory()->create();
        $strategicObjective = StrategicObjective::factory()->create();
        $operationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $strategicObjective->id,
        ]);

        // Set selected department to VILLE
        session([UserRepository::$department_selected_key => DepartmentEnum::VILLE->value]);

        // Action in VILLE department (should be counted)
        $villeLeadingAction = Action::factory()->create([
            'department' => DepartmentEnum::VILLE->value,
            'operational_objective_id' => $operationalObjective->id,
        ]);
        $service->leadingActions()->attach($villeLeadingAction);

        // Another VILLE action as partnering (should be counted)
        $villePartneringAction = Action::factory()->create([
            'department' => DepartmentEnum::VILLE->value,
            'operational_objective_id' => $operationalObjective->id,
        ]);
        $service->partneringActions()->attach($villePartneringAction);

        // Action in CPAS department (should NOT be counted)
        $cpasAction = Action::withoutGlobalScopes()->create([
            'name' => 'CPAS Action',
            'department' => DepartmentEnum::CPAS->value,
            'operational_objective_id' => $operationalObjective->id,
            'user_add' => 'test',
        ]);
        $service->partneringActions()->attach($cpasAction);

        $service->refresh();

        expect($service->leadingActionsForDepartment()->count())->toBe(1)
            ->and($service->partneringActionsForDepartment()->count())->toBe(1);
    });
});
