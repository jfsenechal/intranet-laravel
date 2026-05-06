<?php

declare(strict_types=1);

namespace AcMarche\Pst\Tests\Feature\Filament\Resources\ActionPst;

use AcMarche\Pst\Enums\ActionStateEnum;
use AcMarche\Pst\Enums\ActionSynergyEnum;
use AcMarche\Pst\Enums\ActionTypeEnum;
use AcMarche\Pst\Enums\RolesEnum;
use AcMarche\Pst\Filament\Resources\ActionPst\Pages\EditActionPst;
use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\History;
use AcMarche\Pst\Models\OperationalObjective;
use AcMarche\Pst\Models\Service;
use AcMarche\Pst\Models\StrategicObjective;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ActionHistoryTrackingTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private Action $action;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('pst'));

        $adminRole = Role::factory()->create(['name' => RolesEnum::ADMIN->value]);
        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($adminRole);

        $this->action = $this->createAction();
    }

    public function test_adding_agent_user_creates_history_record(): void
    {
        $newUser = User::factory()->create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->fillForm([
                'action_users' => [$newUser->username],
            ])
            ->call('save')
            ->assertNotified();

        $this->assertDatabaseHas(History::class, [
            'action_id' => $this->action->id,
            'property' => 'users',
            'new_value' => 'Jean Dupont',
        ]);
    }

    public function test_removing_agent_user_creates_history_record(): void
    {
        $existingUser = User::factory()->create([
            'first_name' => 'Marie',
            'last_name' => 'Martin',
        ]);
        $this->action->users()->attach($existingUser);

        $this->actingAs($this->adminUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->fillForm([
                'action_users' => [],
            ])
            ->call('save')
            ->assertNotified();

        $this->assertDatabaseHas(History::class, [
            'action_id' => $this->action->id,
            'property' => 'users',
            'old_value' => 'Marie Martin',
        ]);
    }

    public function test_adding_leader_service_creates_history_record(): void
    {
        $service = Service::factory()->create(['name' => 'Service Informatique']);

        $this->actingAs($this->adminUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->fillForm([
                'action_service_leader' => [$service->id],
            ])
            ->call('save')
            ->assertNotified();

        $this->assertDatabaseHas(History::class, [
            'action_id' => $this->action->id,
            'property' => 'leaderServices',
            'new_value' => 'Service Informatique',
        ]);
    }

    public function test_removing_leader_service_creates_history_record(): void
    {
        $service = Service::factory()->create(['name' => 'Service RH']);
        $this->action->leaderServices()->attach($service);

        $this->actingAs($this->adminUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->fillForm([
                'action_service_leader' => [],
            ])
            ->call('save')
            ->assertNotified();

        $this->assertDatabaseHas(History::class, [
            'action_id' => $this->action->id,
            'property' => 'leaderServices',
            'old_value' => 'Service RH',
        ]);
    }

    public function test_adding_partner_service_creates_history_record(): void
    {
        $service = Service::factory()->create(['name' => 'Service Communication']);

        $this->actingAs($this->adminUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->fillForm([
                'action_service_partner' => [$service->id],
            ])
            ->call('save')
            ->assertNotified();

        $this->assertDatabaseHas(History::class, [
            'action_id' => $this->action->id,
            'property' => 'partnerServices',
            'new_value' => 'Service Communication',
        ]);
    }

    public function test_adding_mandataire_creates_history_record(): void
    {
        $mandataireRole = Role::factory()->create(['name' => RolesEnum::MANDATAIRE->value]);
        $mandataire = User::factory()->create([
            'first_name' => 'Pierre',
            'last_name' => 'Durand',
        ]);
        $mandataire->roles()->attach($mandataireRole);

        $this->actingAs($this->adminUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->fillForm([
                'action_mandatory' => [$mandataire->username],
            ])
            ->call('save')
            ->assertNotified();

        $this->assertDatabaseHas(History::class, [
            'action_id' => $this->action->id,
            'property' => 'mandataries',
            'new_value' => 'Pierre Durand',
        ]);
    }

    public function test_no_history_when_relationships_unchanged(): void
    {
        $user = User::factory()->create();
        $this->action->users()->attach($user);

        $historyCountBefore = History::where('action_id', $this->action->id)->count();

        $this->actingAs($this->adminUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->fillForm([
                'action_users' => [$user->username],
            ])
            ->call('save')
            ->assertNotified();

        $historyCountAfter = History::where('action_id', $this->action->id)
            ->where('property', 'users')
            ->count();

        expect($historyCountAfter)->toBe($historyCountBefore);
    }

    private function createAction(): Action
    {
        $strategicObjective = StrategicObjective::factory()->create();
        $operationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $strategicObjective->id,
        ]);

        return Action::factory()->create([
            'operational_objective_id' => $operationalObjective->id,
            'state' => ActionStateEnum::START,
            'type' => ActionTypeEnum::PST,
            'synergy' => ActionSynergyEnum::NO,
        ]);
    }
}
