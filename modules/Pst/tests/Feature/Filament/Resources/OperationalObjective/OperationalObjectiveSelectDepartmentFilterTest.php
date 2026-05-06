<?php

declare(strict_types=1);

namespace AcMarche\Pst\Tests\Feature\Filament\Resources\OperationalObjective;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Pst\Enums\ActionScopeEnum;
use AcMarche\Pst\Enums\RolesEnum;
use AcMarche\Pst\Filament\Resources\OperationalObjective\Pages\CreateOperationalObjective;
use AcMarche\Pst\Models\StrategicObjective;
use AcMarche\Security\Models\Role;
use AcMarche\Security\Repository\UserRepository;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests for department filtering on Select fields in OperationalObjective forms.
 *
 * These tests verify that:
 * - strategic_objective_id Select only shows objectives matching user's department
 * - Internal strategic objectives (department = null) are always visible
 */
final class OperationalObjectiveSelectDepartmentFilterTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('pst'));

        $this->adminRole = Role::factory()->create(['name' => RolesEnum::ADMIN->value]);
    }

    public function test_user_can_select_strategic_objective_from_same_department(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        session([UserRepository::$department_selected_key => DepartmentEnum::VILLE->value]);

        $villeStrategicObjective = StrategicObjective::factory()->create([
            'department' => DepartmentEnum::VILLE->value,
            'scope' => ActionScopeEnum::EXTERNAL,
        ]);

        Livewire::test(CreateOperationalObjective::class)
            ->fillForm([
                'name' => 'Test Operational Objective',
                'strategic_objective_id' => $villeStrategicObjective->id,
                'department' => DepartmentEnum::VILLE->value,
                'scope' => ActionScopeEnum::EXTERNAL,
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();
    }

    public function test_user_cannot_select_strategic_objective_from_different_department(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        session([UserRepository::$department_selected_key => DepartmentEnum::VILLE->value]);

        // Create strategic objective for CPAS department
        $cpasStrategicObjective = StrategicObjective::factory()->create([
            'department' => DepartmentEnum::CPAS->value,
            'scope' => ActionScopeEnum::EXTERNAL,
        ]);

        Livewire::test(CreateOperationalObjective::class)
            ->fillForm([
                'name' => 'Test Operational Objective',
                'strategic_objective_id' => $cpasStrategicObjective->id,
                'department' => DepartmentEnum::VILLE->value,
                'scope' => ActionScopeEnum::EXTERNAL,
            ])
            ->call('create')
            ->assertHasFormErrors(['strategic_objective_id']);
    }

    public function test_user_can_select_internal_strategic_objective_regardless_of_department(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        session([UserRepository::$department_selected_key => DepartmentEnum::VILLE->value]);

        // Create internal strategic objective (department = null)
        $internalStrategicObjective = StrategicObjective::factory()->create([
            'department' => null,
            'scope' => ActionScopeEnum::INTERNAL,
        ]);

        Livewire::test(CreateOperationalObjective::class)
            ->fillForm([
                'name' => 'Test Internal Operational Objective',
                'strategic_objective_id' => $internalStrategicObjective->id,
                'department' => DepartmentEnum::VILLE->value,
                'scope' => ActionScopeEnum::INTERNAL,
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();
    }

    public function test_cpas_user_can_select_strategic_objective_from_cpas_department(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::CPAS->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        session([UserRepository::$department_selected_key => DepartmentEnum::CPAS->value]);

        $cpasStrategicObjective = StrategicObjective::factory()->create([
            'department' => DepartmentEnum::CPAS->value,
            'scope' => ActionScopeEnum::EXTERNAL,
        ]);

        Livewire::test(CreateOperationalObjective::class)
            ->fillForm([
                'name' => 'Test CPAS Operational Objective',
                'strategic_objective_id' => $cpasStrategicObjective->id,
                'department' => DepartmentEnum::CPAS->value,
                'scope' => ActionScopeEnum::EXTERNAL,
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();
    }

    public function test_cpas_user_cannot_select_strategic_objective_from_ville_department(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::CPAS->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        session([UserRepository::$department_selected_key => DepartmentEnum::CPAS->value]);

        $villeStrategicObjective = StrategicObjective::factory()->create([
            'department' => DepartmentEnum::VILLE->value,
            'scope' => ActionScopeEnum::EXTERNAL,
        ]);

        Livewire::test(CreateOperationalObjective::class)
            ->fillForm([
                'name' => 'Test Operational Objective',
                'strategic_objective_id' => $villeStrategicObjective->id,
                'department' => DepartmentEnum::CPAS->value,
                'scope' => ActionScopeEnum::EXTERNAL,
            ])
            ->call('create')
            ->assertHasFormErrors(['strategic_objective_id']);
    }

    public function test_user_with_both_departments_uses_selected_department_for_filtering(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value, DepartmentEnum::CPAS->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        // User selects CPAS department
        session([UserRepository::$department_selected_key => DepartmentEnum::CPAS->value]);

        $villeStrategicObjective = StrategicObjective::factory()->create([
            'department' => DepartmentEnum::VILLE->value,
            'scope' => ActionScopeEnum::EXTERNAL,
        ]);

        // Even though user has VILLE in their departments, they selected CPAS
        // so VILLE strategic objectives should not be selectable
        Livewire::test(CreateOperationalObjective::class)
            ->fillForm([
                'name' => 'Test Operational Objective',
                'strategic_objective_id' => $villeStrategicObjective->id,
                'department' => DepartmentEnum::CPAS->value,
                'scope' => ActionScopeEnum::EXTERNAL,
            ])
            ->call('create')
            ->assertHasFormErrors(['strategic_objective_id']);
    }
}
