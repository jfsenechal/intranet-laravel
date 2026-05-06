<?php

declare(strict_types=1);

namespace AcMarche\Pst\Tests\Feature\Filament\Resources\ActionPst;

use AcMarche\Pst\Enums\ActionScopeEnum;
use AcMarche\Pst\Enums\RolesEnum;
use AcMarche\Pst\Filament\Resources\ActionPst\Pages\ListActionsPst;
use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\OperationalObjective;
use AcMarche\Pst\Models\StrategicObjective;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ActionTablesTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('pst'));

        $this->adminRole = Role::factory()->create(['name' => RolesEnum::ADMIN->value]);

        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($this->adminRole);
    }

    public function test_can_render_list_actions_page(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ListActionsPst::class)
            ->assertOk();
    }

    public function test_scope_filter_exists(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ListActionsPst::class)
            ->assertTableFilterExists('scope');
    }

    public function test_can_filter_actions_by_scope_internal(): void
    {
        $this->actingAs($this->adminUser);

        $internalStrategicObjective = StrategicObjective::factory()->create([
            'scope' => ActionScopeEnum::INTERNAL->value,
            'department' => 'VILLE',
        ]);
        $externalStrategicObjective = StrategicObjective::factory()->create([
            'scope' => ActionScopeEnum::EXTERNAL->value,
            'department' => 'VILLE',
        ]);

        $internalOperationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $internalStrategicObjective->id,
        ]);
        $externalOperationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $externalStrategicObjective->id,
        ]);

        $internalActions = Action::factory()->count(3)->create([
            'operational_objective_id' => $internalOperationalObjective->id,
            'department' => 'VILLE',
            'validated' => true,
            'scope' => ActionScopeEnum::INTERNAL->value,
        ]);
        $externalActions = Action::factory()->count(2)->create([
            'operational_objective_id' => $externalOperationalObjective->id,
            'department' => 'VILLE',
            'validated' => true,
            'scope' => ActionScopeEnum::EXTERNAL->value,
        ]);

        Livewire::test(ListActionsPst::class)
            ->loadTable()
            ->filterTable('scope', ActionScopeEnum::INTERNAL->value)
            ->assertCanSeeTableRecords($internalActions)
            ->assertCanNotSeeTableRecords($externalActions);
    }

    public function test_can_filter_actions_by_scope_external(): void
    {
        $this->actingAs($this->adminUser);

        $internalStrategicObjective = StrategicObjective::factory()->create([
            'scope' => ActionScopeEnum::INTERNAL->value,
            'department' => 'VILLE',
        ]);
        $externalStrategicObjective = StrategicObjective::factory()->create([
            'scope' => ActionScopeEnum::EXTERNAL->value,
            'department' => 'VILLE',
        ]);

        $internalOperationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $internalStrategicObjective->id,
        ]);
        $externalOperationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $externalStrategicObjective->id,
        ]);

        $internalActions = Action::factory()->count(3)->create([
            'operational_objective_id' => $internalOperationalObjective->id,
            'department' => 'VILLE',
            'validated' => true,
            'scope' => ActionScopeEnum::INTERNAL->value,
        ]);
        $externalActions = Action::factory()->count(2)->create([
            'operational_objective_id' => $externalOperationalObjective->id,
            'department' => 'VILLE',
            'validated' => true,
            'scope' => ActionScopeEnum::EXTERNAL->value,
        ]);

        Livewire::test(ListActionsPst::class)
            ->loadTable()
            ->filterTable('scope', ActionScopeEnum::EXTERNAL->value)
            ->assertCanSeeTableRecords($externalActions)
            ->assertCanNotSeeTableRecords($internalActions);
    }

    public function test_state_filter_exists(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ListActionsPst::class)
            ->assertTableFilterExists('state');
    }

    public function test_type_filter_exists(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ListActionsPst::class)
            ->assertTableFilterExists('type');
    }

    public function test_operational_objectives_filter_exists(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ListActionsPst::class)
            ->assertTableFilterExists('operational_objectives');
    }

    public function test_users_filter_exists(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ListActionsPst::class)
            ->assertTableFilterExists('users');
    }

    public function test_services_filter_exists(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ListActionsPst::class)
            ->assertTableFilterExists('services');
    }
}
