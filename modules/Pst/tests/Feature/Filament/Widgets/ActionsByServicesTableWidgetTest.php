<?php

declare(strict_types=1);

namespace AcMarche\Pst\Tests\Feature\Filament\Widgets;

use AcMarche\Pst\Filament\Widgets\ActionsByServicesTableWidget;
use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\OperationalObjective;
use AcMarche\Pst\Models\Service;
use AcMarche\Pst\Models\StrategicObjective;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ActionsByServicesTableWidgetTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('pst'));

        $this->user = User::factory()->create(['departments' => ['VILLE']]);
    }

    public function test_it_lists_actions_of_the_services_the_user_belongs_to(): void
    {
        $this->actingAs($this->user);

        $leaderService = Service::factory()->create();
        $partnerService = Service::factory()->create();
        $otherService = Service::factory()->create();

        $this->user->services()->attach([$leaderService->id, $partnerService->id]);

        $strategicObjective = StrategicObjective::factory()->create(['department' => 'VILLE']);
        $operationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $strategicObjective->id,
        ]);

        $leadingAction = Action::factory()->create([
            'operational_objective_id' => $operationalObjective->id,
            'department' => 'VILLE',
        ]);
        $leadingAction->leaderServices()->attach($leaderService->id);

        $partneringAction = Action::factory()->create([
            'operational_objective_id' => $operationalObjective->id,
            'department' => 'VILLE',
        ]);
        $partneringAction->partnerServices()->attach($partnerService->id);

        $unrelatedAction = Action::factory()->create([
            'operational_objective_id' => $operationalObjective->id,
            'department' => 'VILLE',
        ]);
        $unrelatedAction->leaderServices()->attach($otherService->id);

        Livewire::test(ActionsByServicesTableWidget::class)
            ->loadTable()
            ->assertOk()
            ->assertCanSeeTableRecords([$leadingAction, $partneringAction])
            ->assertCanNotSeeTableRecords([$unrelatedAction]);
    }
}
