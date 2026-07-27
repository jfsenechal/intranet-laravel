<?php

declare(strict_types=1);

namespace AcMarche\Pst\Tests\Feature\Filament\Resources\ActionPst;

use AcMarche\Pst\Enums\RolesEnum;
use AcMarche\Pst\Filament\Resources\ActionPst\Pages\ViewActionPst;
use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\OperationalObjective;
use AcMarche\Pst\Models\StrategicObjective;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReminderActionTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $adminUser;

    private Action $action;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('pst'));

        $adminRole = Role::factory()->create(['name' => RolesEnum::ADMIN->value]);

        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($adminRole);

        $strategicObjective = StrategicObjective::factory()->create();
        $operationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $strategicObjective->id,
        ]);

        $this->action = Action::factory()->create([
            'operational_objective_id' => $operationalObjective->id,
        ]);
    }

    public function test_view_page_loads_when_action_has_pilot_agents(): void
    {
        $pilot = User::factory()->create();
        $this->action->users()->attach($pilot);

        $this->actingAs($this->adminUser);

        Livewire::test(ViewActionPst::class, ['record' => $this->action->id])
            ->assertOk();
    }

    public function test_reminder_action_prefills_recipients_from_pivot(): void
    {
        $pilotOne = User::factory()->create();
        $pilotTwo = User::factory()->create();
        $this->action->users()->attach([$pilotOne->id, $pilotTwo->id]);

        $this->actingAs($this->adminUser);

        Livewire::test(ViewActionPst::class, ['record' => $this->action->id])
            ->mountAction('reminder')
            ->assertSchemaStateSet([
                'recipients' => [$pilotOne->username, $pilotTwo->username],
            ]);
    }
}
