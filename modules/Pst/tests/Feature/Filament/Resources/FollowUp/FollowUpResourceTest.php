<?php

declare(strict_types=1);

use AcMarche\Pst\Enums\RoleEnum;
use AcMarche\Pst\Filament\Resources\ActionPst\Pages\ViewActionPst;
use AcMarche\Pst\Filament\Resources\ActionPst\RelationManagers\FollowUpsRelationManager;
use AcMarche\Pst\Filament\Resources\FollowUp\Pages\CreateFollowUp;
use AcMarche\Pst\Filament\Resources\FollowUp\Pages\EditFollowUp;
use AcMarche\Pst\Filament\Resources\FollowUp\Pages\ListFollowUps;
use AcMarche\Pst\Filament\Resources\FollowUp\Pages\ViewFollowUp;
use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\FollowUp;
use AcMarche\Pst\Models\OperationalObjective;
use AcMarche\Pst\Models\StrategicObjective;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('pst'));
    $adminRole = Role::factory()->create(['name' => RoleEnum::ADMIN->value]);
    $this->adminUser = User::factory()->create();
    $this->adminUser->roles()->attach($adminRole);

    $this->actingAs($this->adminUser);

    $strategicObjective = StrategicObjective::factory()->create();
    $operationalObjective = OperationalObjective::factory()->create([
        'strategic_objective_id' => $strategicObjective->id,
    ]);
    $this->action = Action::factory()->create([
        'operational_objective_id' => $operationalObjective->id,
    ]);
});

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListFollowUps::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateFollowUp::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = FollowUp::factory()->create([
            'action_id' => $this->action->id,
        ]);

        Livewire::test(ViewFollowUp::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = FollowUp::factory()->create([
            'action_id' => $this->action->id,
        ]);

        Livewire::test(EditFollowUp::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });
});

describe('relation manager', function (): void {
    it('can render the FollowUpsRelationManager', function (): void {
        FollowUp::factory(3)->create([
            'action_id' => $this->action->id,
        ]);

        Livewire::test(FollowUpsRelationManager::class, [
            'ownerRecord' => $this->action,
            'pageClass' => ViewActionPst::class,
        ])
            ->assertOk();
    });

    it('can list followups in relation manager', function (): void {
        $followups = FollowUp::factory(3)->create([
            'action_id' => $this->action->id,
        ]);

        Livewire::test(FollowUpsRelationManager::class, [
            'ownerRecord' => $this->action,
            'pageClass' => ViewActionPst::class,
        ])
            ->loadTable()
            ->assertCanSeeTableRecords($followups);
    });

    it('can create a followup through relation manager', function (): void {
        Livewire::test(FollowUpsRelationManager::class, [
            'ownerRecord' => $this->action,
            'pageClass' => ViewActionPst::class,
        ])
            ->callTableAction('create', data: [
                'content' => '<p>Test followup content</p>',
            ])
            ->assertNotified();

        assertDatabaseHas(FollowUp::class, [
            'action_id' => $this->action->id,
            'content' => '<p>Test followup content</p>',
        ]);
    });

    it('can edit a followup through relation manager', function (): void {
        $followup = FollowUp::factory()->create([
            'action_id' => $this->action->id,
            'content' => '<p>Original content</p>',
        ]);

        Livewire::test(FollowUpsRelationManager::class, [
            'ownerRecord' => $this->action,
            'pageClass' => ViewActionPst::class,
        ])
            ->callTableAction('edit', $followup, data: [
                'content' => '<p>Updated content</p>',
            ])
            ->assertNotified();

        assertDatabaseHas(FollowUp::class, [
            'id' => $followup->id,
            'content' => '<p>Updated content</p>',
        ]);
    });

    it('can delete a followup through relation manager', function (): void {
        $followup = FollowUp::factory()->create([
            'action_id' => $this->action->id,
        ]);

        Livewire::test(FollowUpsRelationManager::class, [
            'ownerRecord' => $this->action,
            'pageClass' => ViewActionPst::class,
        ])
            ->callTableAction('delete', $followup)
            ->assertNotified();

        assertDatabaseMissing($followup);
    });

    it('validates content is required in relation manager', function (): void {
        $initialCount = FollowUp::count();

        Livewire::test(FollowUpsRelationManager::class, [
            'ownerRecord' => $this->action,
            'pageClass' => ViewActionPst::class,
        ])
            ->callTableAction('create', data: [
                'content' => '',
            ])
            ->assertNotNotified();

        expect(FollowUp::count())->toBe($initialCount);
    });
});

describe('form fields', function (): void {
    it('has content field', function (): void {
        Livewire::test(CreateFollowUp::class)
            ->assertFormFieldExists('content');
    });

    it('has icon field', function (): void {
        Livewire::test(CreateFollowUp::class)
            ->assertFormFieldExists('icon');
    });
});
