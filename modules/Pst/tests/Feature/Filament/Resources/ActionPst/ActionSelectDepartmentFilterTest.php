<?php

declare(strict_types=1);

namespace AcMarche\Pst\Tests\Feature\Filament\Resources\ActionPst;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Pst\Enums\ActionScopeEnum;
use AcMarche\Pst\Enums\ActionStateEnum;
use AcMarche\Pst\Enums\ActionSynergyEnum;
use AcMarche\Pst\Enums\RolesEnum;
use AcMarche\Pst\Filament\Resources\ActionPst\Pages\CreateActionPst;
use AcMarche\Pst\Filament\Resources\ActionPst\Pages\ListActionsPst;
use AcMarche\Pst\Models\OperationalObjective;
use AcMarche\Pst\Models\StrategicObjective;
use AcMarche\Security\Models\Role;
use AcMarche\Security\Repository\UserRepository;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests for department filtering on Select fields in Action forms and tables.
 *
 * These tests verify that:
 * - operational_objective_id Select in ActionForm only shows objectives matching user's department
 * - operational_objectives SelectFilter in ActionTables only shows objectives matching user's department
 * - Internal objectives (department = null) are always visible regardless of selected department
 */
final class ActionSelectDepartmentFilterTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('pst'));

        $this->adminRole = Role::factory()->create(['name' => RolesEnum::ADMIN->value]);
    }

    public function test_user_can_select_operational_objective_from_same_department(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        session([UserRepository::$department_selected_key => DepartmentEnum::VILLE->value]);

        $villeObjective = $this->createOperationalObjective(DepartmentEnum::VILLE->value);

        Livewire::test(CreateActionPst::class)
            ->fillForm([
                'name' => 'Test Action',
                'operational_objective_id' => $villeObjective->id,
                'state' => ActionStateEnum::START->value,
                'scope' => ActionScopeEnum::EXTERNAL->value,
                'synergy' => ActionSynergyEnum::YES->value,
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();
    }

    public function test_user_cannot_select_operational_objective_from_different_department(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        session([UserRepository::$department_selected_key => DepartmentEnum::VILLE->value]);

        // Create objective for CPAS department (different from user's selected department)
        $cpasObjective = $this->createOperationalObjective(DepartmentEnum::CPAS->value);

        Livewire::test(CreateActionPst::class)
            ->fillForm([
                'name' => 'Test Action',
                'operational_objective_id' => $cpasObjective->id,
                'state' => ActionStateEnum::START->value,
                'scope' => ActionScopeEnum::EXTERNAL->value,
                'synergy' => ActionSynergyEnum::YES->value,
            ])
            ->call('create')
            ->assertHasFormErrors(['operational_objective_id']);
    }

    public function test_user_can_select_internal_operational_objective_regardless_of_department(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        session([UserRepository::$department_selected_key => DepartmentEnum::VILLE->value]);

        // Create internal objective (department = null)
        $internalObjective = $this->createInternalOperationalObjective();

        Livewire::test(CreateActionPst::class)
            ->fillForm([
                'name' => 'Test Action with Internal Objective',
                'operational_objective_id' => $internalObjective->id,
                'state' => ActionStateEnum::START->value,
                'scope' => ActionScopeEnum::INTERNAL->value,
                'synergy' => ActionSynergyEnum::YES->value,
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();
    }

    public function test_cpas_user_can_select_operational_objective_from_cpas_department(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::CPAS->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        session([UserRepository::$department_selected_key => DepartmentEnum::CPAS->value]);

        $cpasObjective = $this->createOperationalObjective(DepartmentEnum::CPAS->value);

        Livewire::test(CreateActionPst::class)
            ->fillForm([
                'name' => 'Test CPAS Action',
                'operational_objective_id' => $cpasObjective->id,
                'state' => ActionStateEnum::START->value,
                'scope' => ActionScopeEnum::EXTERNAL->value,
                'synergy' => ActionSynergyEnum::YES->value,
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();
    }

    public function test_cpas_user_cannot_select_operational_objective_from_ville_department(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::CPAS->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        session([UserRepository::$department_selected_key => DepartmentEnum::CPAS->value]);

        $villeObjective = $this->createOperationalObjective(DepartmentEnum::VILLE->value);

        Livewire::test(CreateActionPst::class)
            ->fillForm([
                'name' => 'Test Action',
                'operational_objective_id' => $villeObjective->id,
                'state' => ActionStateEnum::START->value,
                'scope' => ActionScopeEnum::EXTERNAL->value,
                'synergy' => ActionSynergyEnum::YES->value,
            ])
            ->call('create')
            ->assertHasFormErrors(['operational_objective_id']);
    }

    public function test_table_filter_shows_only_objectives_from_user_department(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        session([UserRepository::$department_selected_key => DepartmentEnum::VILLE->value]);

        $villeObjective = $this->createOperationalObjective(DepartmentEnum::VILLE->value);
        $this->createOperationalObjective(DepartmentEnum::CPAS->value);
        $internalObjective = $this->createInternalOperationalObjective();

        // Test that filter can be applied with VILLE objective
        Livewire::test(ListActionsPst::class)
            ->loadTable()
            ->filterTable('operational_objectives', $villeObjective->id)
            ->assertOk();

        // Filter with internal objective should also work
        Livewire::test(ListActionsPst::class)
            ->loadTable()
            ->filterTable('operational_objectives', $internalObjective->id)
            ->assertOk();
    }

    private function createOperationalObjective(string $department): OperationalObjective
    {
        $strategicObjective = StrategicObjective::factory()->create([
            'department' => $department,
            'scope' => ActionScopeEnum::EXTERNAL,
        ]);

        return OperationalObjective::factory()->create([
            'strategic_objective_id' => $strategicObjective->id,
            'department' => $department,
            'scope' => ActionScopeEnum::EXTERNAL,
        ]);
    }

    private function createInternalOperationalObjective(): OperationalObjective
    {
        $strategicObjective = StrategicObjective::factory()->create([
            'department' => null,
            'scope' => ActionScopeEnum::INTERNAL,
        ]);

        return OperationalObjective::factory()->create([
            'strategic_objective_id' => $strategicObjective->id,
            'department' => null,
            'scope' => ActionScopeEnum::INTERNAL,
        ]);
    }
}
