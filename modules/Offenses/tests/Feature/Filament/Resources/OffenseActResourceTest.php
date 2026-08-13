<?php

declare(strict_types=1);

use AcMarche\Offenses\Filament\Resources\OffenseActs\OffenseActResource;
use AcMarche\Offenses\Filament\Resources\OffenseActs\Pages\ListOffenseActs;
use AcMarche\Offenses\Filament\Resources\OffenseActs\Pages\ViewOffenseAct;
use AcMarche\Offenses\Filament\Resources\OffenseActs\RelationManagers\OffensesRelationManager;
use AcMarche\Offenses\Models\Offender;
use AcMarche\Offenses\Models\Offense;
use AcMarche\Offenses\Models\OffenseAct;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('offenses-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $this->act = OffenseAct::create(['name' => 'Dépôt sauvage']);
    $this->offender = Offender::create(['last_name' => 'Dupont', 'first_name' => 'Jean']);
});

it('renders the view page of an offense act without the form', function (): void {
    livewire(ViewOffenseAct::class, ['record' => $this->act->id])
        ->assertOk()
        // Only the relation manager is rendered, not the disabled form Filament
        // falls back to when a resource has no infolist.
        ->assertDontSeeHtml('data.name')
        ->assertSeeHtml('OffensesRelationManager');
});

it('lists the offenses linked to the act', function (): void {
    $offense = Offense::create([
        'offender_id' => $this->offender->id,
        'offense_act_id' => $this->act->id,
        'decision_date' => '2026-03-12',
        'fine_amount' => 120.0,
    ]);

    $otherAct = OffenseAct::create(['name' => 'Tapage nocturne']);
    $otherOffense = Offense::create([
        'offender_id' => $this->offender->id,
        'offense_act_id' => $otherAct->id,
    ]);

    livewire(OffensesRelationManager::class, [
        'ownerRecord' => $this->act,
        'pageClass' => ViewOffenseAct::class,
    ])
        ->assertOk()
        ->loadTable()
        ->assertCanSeeTableRecords([$offense])
        ->assertCanNotSeeTableRecords([$otherOffense])
        ->assertSee('Dupont');
});

it('refuses to delete an act that is used by an offense', function (): void {
    Offense::create([
        'offender_id' => $this->offender->id,
        'offense_act_id' => $this->act->id,
    ]);

    // The model itself refuses, so every delete path is covered, not just the button.
    expect($this->act->delete())->toBeFalse();

    livewire(ViewOffenseAct::class, ['record' => $this->act->id])
        ->callAction(DeleteAction::class)
        ->assertNotified('Suppression impossible');

    assertDatabaseHas(OffenseAct::class, ['id' => $this->act->id]);
});

it('deletes an act that no offense uses', function (): void {
    livewire(ViewOffenseAct::class, ['record' => $this->act->id])
        ->callAction(DeleteAction::class)
        ->assertNotified();

    assertDatabaseMissing(OffenseAct::class, ['id' => $this->act->id]);
});

it('deletes only the unused acts in a bulk delete', function (): void {
    Offense::create([
        'offender_id' => $this->offender->id,
        'offense_act_id' => $this->act->id,
    ]);

    $unused = OffenseAct::create(['name' => 'Tapage nocturne']);

    livewire(ListOffenseActs::class)
        ->selectTableRecords([$this->act->id, $unused->id])
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
        ->assertNotified();

    assertDatabaseHas(OffenseAct::class, ['id' => $this->act->id]);
    assertDatabaseMissing(OffenseAct::class, ['id' => $unused->id]);
});

it('tells the user how many offenses block the deletion', function (): void {
    expect(OffenseActResource::deletionBlockedMessage($this->act))->toBeNull();

    Offense::create(['offender_id' => $this->offender->id, 'offense_act_id' => $this->act->id]);

    expect(OffenseActResource::deletionBlockedMessage($this->act))
        ->toContain('utilisé par 1 incivilité');

    Offense::create(['offender_id' => $this->offender->id, 'offense_act_id' => $this->act->id]);

    expect(OffenseActResource::deletionBlockedMessage($this->act))
        ->toContain('utilisé par 2 incivilités');
});
