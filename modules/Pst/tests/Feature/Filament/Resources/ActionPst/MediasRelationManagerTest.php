<?php

declare(strict_types=1);

namespace AcMarche\Pst\Tests\Feature\Filament\Resources\ActionPst;

use AcMarche\Pst\Enums\RolesEnum;
use AcMarche\Pst\Filament\Resources\ActionPst\Pages\EditActionPst;
use AcMarche\Pst\Filament\Resources\ActionPst\RelationManagers\MediasRelationManager;
use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\Media;
use AcMarche\Pst\Models\OperationalObjective;
use AcMarche\Pst\Models\StrategicObjective;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The initial pst migration bails out when the database already exists, so a legacy
 * database kept the pre-rename `media` table while the model pointed at `pst_media`,
 * and every render of this relation manager blew up on a missing table.
 */
final class MediasRelationManagerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private User $adminUser;

    private Action $action;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('pst-panel'));

        $adminRole = Role::factory()->create(['name' => RolesEnum::ADMIN->value]);
        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($adminRole);

        $this->action = $this->createAction();
    }

    public function test_medias_is_resolved_as_a_relation_and_not_as_a_cast_attribute(): void
    {
        $media = $this->createMedia();

        $this->assertCount(1, $this->action->medias);
        $this->assertTrue($this->action->medias->first()->is($media));
    }

    public function test_relation_manager_lists_the_medias_of_the_action(): void
    {
        $media = $this->createMedia();

        $this->actingAs($this->adminUser);

        Livewire::test(MediasRelationManager::class, [
            'ownerRecord' => $this->action,
            'pageClass' => EditActionPst::class,
        ])
            ->assertSuccessful()
            ->loadTable()
            ->assertCanSeeTableRecords([$media]);
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

    private function createMedia(): Media
    {
        return Media::factory()->create([
            'action_id' => $this->action->id,
            'name' => 'Rapport annuel',
            'file_name' => 'rapport-annuel.pdf',
            'mime_type' => 'application/pdf',
        ]);
    }
}
