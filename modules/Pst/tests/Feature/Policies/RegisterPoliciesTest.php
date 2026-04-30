<?php

declare(strict_types=1);

namespace AcMarche\Pst\Tests\Feature\Policies;

use AcMarche\Pst\Enums\RoleEnum;
use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\OperationalObjective;
use AcMarche\Pst\Models\Service;
use AcMarche\Pst\Models\StrategicObjective;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

final class RegisterPoliciesTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $regularUser;

    private User $mandataireUser;

    private Role $adminRole;

    private Role $mandataireRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::factory()->create(['name' => RoleEnum::ADMIN->value]);
        $this->mandataireRole = Role::factory()->create(['name' => RoleEnum::MANDATAIRE->value]);

        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($this->adminRole);

        $this->mandataireUser = User::factory()->create();
        $this->mandataireUser->roles()->attach($this->mandataireRole);

        $this->regularUser = User::factory()->create();
    }

    public function test_teams_edit_gate_allows_create_operation_for_any_user(): void
    {
        $action = $this->createAction();

        $this->actingAs($this->regularUser);

        $result = Gate::check('teams-edit', [$action, 'create']);

        $this->assertTrue($result);
    }

    public function test_teams_edit_gate_allows_admin_on_edit_operation(): void
    {
        $action = $this->createAction();

        $this->actingAs($this->adminUser);

        $result = Gate::check('teams-edit', [$action, 'edit']);

        $this->assertTrue($result);
    }

    public function test_teams_edit_gate_denies_regular_user_on_edit_operation(): void
    {
        $action = $this->createAction();

        $this->actingAs($this->regularUser);

        $result = Gate::check('teams-edit', [$action, 'edit']);

        $this->assertFalse($result);
    }

    // ActionPolicy: viewAny
    public function test_action_policy_view_any_allows_any_user(): void
    {
        $this->actingAs($this->regularUser);

        $result = Gate::check('viewAny', Action::class);

        $this->assertTrue($result);
    }

    // ActionPolicy: view
    public function test_action_policy_view_allows_any_user(): void
    {
        $action = $this->createAction();

        $this->actingAs($this->regularUser);

        $result = Gate::check('view', $action);

        $this->assertTrue($result);
    }

    // ActionPolicy: create
    public function test_action_policy_create_allows_regular_user(): void
    {
        $this->actingAs($this->regularUser);

        $result = Gate::check('create', Action::class);

        $this->assertTrue($result);
    }

    public function test_action_policy_create_allows_admin(): void
    {
        $this->actingAs($this->adminUser);

        $result = Gate::check('create', Action::class);

        $this->assertTrue($result);
    }

    public function test_action_policy_create_denies_mandataire(): void
    {
        $this->actingAs($this->mandataireUser);

        $result = Gate::check('create', Action::class);

        $this->assertFalse($result);
    }

    // ActionPolicy: update
    public function test_action_policy_update_allows_admin(): void
    {
        $action = $this->createAction();

        $this->actingAs($this->adminUser);

        $result = Gate::check('update', $action);

        $this->assertTrue($result);
    }

    public function test_action_policy_update_denies_mandataire(): void
    {
        $action = $this->createAction();

        $this->actingAs($this->mandataireUser);

        $result = Gate::check('update', $action);

        $this->assertFalse($result);
    }

    public function test_action_policy_update_allows_user_directly_linked_to_action(): void
    {
        $action = $this->createAction();
        $action->users()->attach($this->regularUser);

        $this->actingAs($this->regularUser);

        $result = Gate::check('update', $action);

        $this->assertTrue($result);
    }

    public function test_action_policy_update_allows_user_in_leader_service(): void
    {
        $action = $this->createAction();
        $service = Service::factory()->create();
        $service->users()->attach($this->regularUser);
        $action->leaderServices()->attach($service);

        $this->actingAs($this->regularUser);

        $result = Gate::check('update', $action);

        $this->assertTrue($result);
    }

    public function test_action_policy_update_denies_unlinked_regular_user(): void
    {
        $action = $this->createAction();

        $this->actingAs($this->regularUser);

        $result = Gate::check('update', $action);

        $this->assertFalse($result);
    }

    // ActionPolicy: delete
    public function test_action_policy_delete_allows_admin(): void
    {
        $action = $this->createAction();

        $this->actingAs($this->adminUser);

        $result = Gate::check('delete', $action);

        $this->assertTrue($result);
    }

    public function test_action_policy_delete_denies_mandataire(): void
    {
        $action = $this->createAction();

        $this->actingAs($this->mandataireUser);

        $result = Gate::check('delete', $action);

        $this->assertFalse($result);
    }

    public function test_action_policy_delete_allows_user_directly_linked_to_action(): void
    {
        $action = $this->createAction();
        $action->users()->attach($this->regularUser);

        $this->actingAs($this->regularUser);

        $result = Gate::check('delete', $action);

        $this->assertTrue($result);
    }

    public function test_action_policy_delete_denies_unlinked_regular_user(): void
    {
        $action = $this->createAction();

        $this->actingAs($this->regularUser);

        $result = Gate::check('delete', $action);

        $this->assertFalse($result);
    }

    // ActionPolicy: restore
    public function test_action_policy_restore_allows_admin(): void
    {
        $action = $this->createAction();
        $action->delete();

        $this->actingAs($this->adminUser);

        $result = Gate::check('restore', $action);

        $this->assertTrue($result);
    }

    public function test_action_policy_restore_denies_regular_user(): void
    {
        $action = $this->createAction();
        $action->delete();

        $this->actingAs($this->regularUser);

        $result = Gate::check('restore', $action);

        $this->assertFalse($result);
    }

    public function test_action_policy_restore_denies_mandataire(): void
    {
        $action = $this->createAction();
        $action->delete();

        $this->actingAs($this->mandataireUser);

        $result = Gate::check('restore', $action);

        $this->assertFalse($result);
    }

    public function test_soft_deleted_action_remains_in_database(): void
    {
        $action = $this->createAction();
        $actionId = $action->id;

        $action->delete();

        $this->assertSoftDeleted('actions', ['id' => $actionId]);
        $this->assertNotNull(Action::withTrashed()->find($actionId));
        $this->assertNull(Action::find($actionId));
    }

    // ActionPolicy: forceDelete
    public function test_action_policy_force_delete_denies_all_users(): void
    {
        $action = $this->createAction();

        $this->actingAs($this->adminUser);

        $result = Gate::check('forceDelete', $action);

        $this->assertFalse($result);
    }

    private function createAction(): Action
    {
        $strategicObjective = StrategicObjective::factory()->create();
        $operationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $strategicObjective->id,
        ]);

        return Action::factory()->create([
            'operational_objective_id' => $operationalObjective->id,
        ]);
    }
}
