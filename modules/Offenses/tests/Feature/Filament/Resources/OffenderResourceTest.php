<?php

declare(strict_types=1);

use AcMarche\Offenses\Filament\Resources\Offenders\Pages\CreateOffender;
use AcMarche\Offenses\Filament\Resources\Offenders\Pages\ViewOffender;
use AcMarche\Offenses\Filament\Resources\Offenders\RelationManagers\OffensesRelationManager;
use AcMarche\Offenses\Models\Offender;
use AcMarche\Offenses\Models\Offense;
use AcMarche\Offenses\Models\OffenseAct;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('offenses-panel'));

    $this->admin = User::factory()->create(['is_administrator' => true]);

    $this->actingAs($this->admin);
});

it('creates an offender from the form, which does not expose the slug', function (): void {
    livewire(CreateOffender::class)
        ->fillForm([
            'last_name' => 'SENECHAL',
            'first_name' => 'Jean-François',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Offender::class, [
        'last_name' => 'SENECHAL',
        'first_name' => 'Jean-François',
        // Legacy slugs turn hyphens into underscores too (liebens_jean_pierre).
        'slug' => 'senechal_jean_francois',
    ]);
});

it('suffixes the slug when two offenders share the same name', function (): void {
    $first = Offender::create(['last_name' => 'Anonyme', 'first_name' => 'Anonyme']);
    $second = Offender::create(['last_name' => 'Anonyme', 'first_name' => 'Anonyme']);
    $third = Offender::create(['last_name' => 'Anonyme', 'first_name' => 'Anonyme']);

    expect($first->slug)->toBe('anonyme_anonyme')
        ->and($second->slug)->toBe('anonyme_anonyme_1')
        ->and($third->slug)->toBe('anonyme_anonyme_2');
});

it('keeps a slug that was provided explicitly', function (): void {
    $offender = Offender::create([
        'slug' => 'legacy_slug',
        'last_name' => 'Dupont',
        'first_name' => 'Jean',
    ]);

    expect($offender->slug)->toBe('legacy_slug');
});

it('stays within the length of the slug column for very long names', function (): void {
    $offender = Offender::create([
        'last_name' => str_repeat('Vandenberghe', 8),
        'first_name' => 'Jean-Baptiste',
    ]);

    expect(mb_strlen($offender->slug))->toBeLessThanOrEqual(70);
});

it('lists the offenses of the offender on the view page', function (): void {
    $offender = Offender::create(['last_name' => 'Dupont', 'first_name' => 'Jean']);
    $act = OffenseAct::create(['name' => 'Dépôt sauvage']);

    $offense = Offense::create([
        'offender_id' => $offender->id,
        'offense_act_id' => $act->id,
        'decision_date' => '2026-03-12',
        'fine_amount' => 120.0,
    ]);

    $otherOffense = Offense::create([
        'offender_id' => Offender::create(['last_name' => 'Martin', 'first_name' => 'Paul'])->id,
        'offense_act_id' => $act->id,
    ]);

    livewire(ViewOffender::class, ['record' => $offender->id])->assertOk();

    livewire(OffensesRelationManager::class, [
        'ownerRecord' => $offender,
        'pageClass' => ViewOffender::class,
    ])
        ->assertOk()
        ->loadTable()
        ->assertCanSeeTableRecords([$offense])
        ->assertCanNotSeeTableRecords([$otherOffense])
        ->assertSee('Dépôt sauvage');
});

it('records the author of an offender', function (): void {
    $offender = Offender::create(['last_name' => 'Dupont', 'first_name' => 'Jean']);

    expect($offender->user_add)->toBe($this->admin->username);
});
