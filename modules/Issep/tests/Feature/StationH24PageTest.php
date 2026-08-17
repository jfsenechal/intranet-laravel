<?php

declare(strict_types=1);

use AcMarche\Issep\Enums\RolesEnum;
use AcMarche\Issep\Filament\Pages\StationH24;
use AcMarche\Issep\Tests\IssepApiFake;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Http;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    /*
     * The fixtures build their timestamps relative to now when the HTTP stub answers, while
     * the assertions rebuild them later: without a frozen clock a tick between the two makes
     * the record keys disagree.
     */
    $this->freezeTime();

    Filament::setCurrentPanel(Filament::getPanel('issep-panel'));

    IssepApiFake::fake();

    $role = Role::factory()->create(['name' => RolesEnum::ROLE_CAPTEUR->value]);
    $user = User::factory()->create(['is_administrator' => false]);
    $user->roles()->attach($role);

    $this->actingAs($user);
});

describe('page', function (): void {
    it('names the station in its title', function (): void {
        livewire(StationH24::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->assertSuccessful()
            ->assertSee('Chaussée de Liège');
    });

    it('shows the last reading of the station', function (): void {
        livewire(StationH24::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->assertSee('Bien (3)')
            ->assertSeeHtml('fi-color-belaqi-3');
    });

    it('says so when the station has no reading of its own', function (): void {
        livewire(StationH24::class, ['station' => IssepApiFake::STATION_WITHOUT_READING])
            ->assertSuccessful()
            ->assertSee('Pas de dernier relevé pour cette station.');
    });

    it('404s on a station the network does not have', function (): void {
        livewire(StationH24::class, ['station' => 99999])->assertNotFound();
    });

    it('is not offered in the navigation', function (): void {
        expect(StationH24::shouldRegisterNavigation())->toBeFalse();
    });
});

describe('history', function (): void {
    /**
     * The fixture holds three readings for this station, one of them three days old: the
     * default period is the last 24 hours, so that one must not show.
     */
    it('shows only the readings of the last 24 hours by default', function (): void {
        livewire(StationH24::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->loadTable()
            ->assertCanSeeTableRecords([
                now()->subHour()->setTimezone(IssepApiFake::TIMEZONE)->format('YmdHis'),
                now()->subHours(5)->setTimezone(IssepApiFake::TIMEZONE)->format('YmdHis'),
            ])
            ->assertCanNotSeeTableRecords([
                now()->subDays(3)->setTimezone(IssepApiFake::TIMEZONE)->format('YmdHis'),
            ]);
    });

    it('shows the older readings when the whole history is asked for', function (): void {
        livewire(StationH24::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->loadTable()
            ->filterTable('period', 'all')
            ->assertCanSeeTableRecords([
                now()->subDays(3)->setTimezone(IssepApiFake::TIMEZONE)->format('YmdHis'),
            ]);
    });

    it('shows nothing for a station that never reported', function (): void {
        livewire(StationH24::class, ['station' => IssepApiFake::STATION_WITHOUT_READING])
            ->loadTable()
            ->assertSee('Aucun relevé sur la période');
    });

    it('reports a failure of the history endpoint', function (): void {
        IssepApiFake::fake(['belaqi' => Http::response(status: 500)]);

        livewire(StationH24::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->assertSuccessful()
            ->loadTable()
            ->assertNotified();
    });
});
