<?php

declare(strict_types=1);

use AcMarche\Offenses\Filament\Resources\Offenses\Pages\CreateOffense;
use AcMarche\Offenses\Models\Offender;
use AcMarche\Offenses\Models\Offense;
use AcMarche\Offenses\Models\OffenseAct;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('offenses-panel'));

    $this->admin = User::factory()->create(['is_administrator' => true]);

    $this->actingAs($this->admin);

    $this->offender = Offender::create(['last_name' => 'Dupont', 'first_name' => 'Jean']);
    $this->act = OffenseAct::create(['name' => 'Dépôt sauvage']);

    // `offender_id` is only present on the initial page load, never on the Livewire update
    // requests that follow (file uploads, live fields, save).
    $this->createPage = fn () => Livewire::withQueryParams(['offender_id' => $this->offender->id])
        ->test(CreateOffense::class);
});

it('creates an offense for the offender given in the query string', function (): void {
    ($this->createPage)()
        ->assertOk()
        ->assertSee('Dupont')
        ->fillForm(['offense_act_id' => $this->act->id])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Offense::class, [
        'offender_id' => $this->offender->id,
        'offense_act_id' => $this->act->id,
    ]);
});

it('aborts with a 404 when no offender is given', function (): void {
    $this->get(CreateOffense::getUrl())->assertNotFound();
});

it('stores an uploaded file on the offenses disk', function (): void {
    Storage::fake($disk = config('offenses.storage.disk'));

    ($this->createPage)()
        ->fillForm([
            'offense_act_id' => $this->act->id,
            'file_name' => [UploadedFile::fake()->create('pv.pdf', 100, 'application/pdf')],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $offense = Offense::query()->latest('id')->first();

    expect($offense->file_name)->toStartWith(config('offenses.storage.directory').'/');

    Storage::disk($disk)->assertExists($offense->file_name);
});
