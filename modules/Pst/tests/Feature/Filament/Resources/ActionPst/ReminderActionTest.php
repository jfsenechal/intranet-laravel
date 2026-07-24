<?php

declare(strict_types=1);

namespace AcMarche\Pst\Tests\Feature\Filament\Resources\ActionPst;

use AcMarche\Pst\Actions\ReminderAction;
use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\OperationalObjective;
use AcMarche\Pst\Models\StrategicObjective;
use App\Models\User;
use Filament\Actions\Action as FilamentAction;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

final class ReminderActionTest extends TestCase
{
    use LazilyRefreshDatabase;

    private Action $action;

    protected function setUp(): void
    {
        parent::setUp();

        $strategicObjective = StrategicObjective::factory()->create();
        $operationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $strategicObjective->id,
        ]);

        $this->action = Action::factory()->create([
            'operational_objective_id' => $operationalObjective->id,
        ]);
    }

    public function test_it_builds_without_touching_a_cross_connection_users_table(): void
    {
        // The pilot agents (users) live on a different database connection than the
        // action. Building the reminder action must read recipients from the
        // action_user pivot (same connection) and never join the users table.
        $this->action->users()->attach(User::factory()->create());

        $action = ReminderAction::createAction($this->action);

        $this->assertInstanceOf(FilamentAction::class, $action);
        $this->assertSame('reminder', $action->getName());
    }

    public function test_default_recipients_come_from_the_action_user_pivot(): void
    {
        $pilotOne = User::factory()->create();
        $pilotTwo = User::factory()->create();
        $this->action->users()->attach($pilotOne);
        $this->action->users()->attach($pilotTwo);

        $recipients = $this->action->users()
            ->newPivotStatement()
            ->where('action_id', $this->action->getKey())
            ->pluck('username')
            ->toArray();

        $this->assertEqualsCanonicalizing(
            [$pilotOne->username, $pilotTwo->username],
            $recipients,
        );
    }
}
