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
     * The index is hourly but a station publishes few of them a day, so the page opens on
     * three days rather than the 24 hours its route is named after. The fixture's five day old
     * reading is outside that window.
     */
    it('shows the readings of the last three days by default', function (): void {
        livewire(StationH24::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->loadTable()
            ->assertCanSeeTableRecords([
                now()->subHour()->format('YmdHis'),
                now()->subHours(5)->format('YmdHis'),
                now()->subDays(2)->format('YmdHis'),
            ])
            ->assertCanNotSeeTableRecords([
                now()->subDays(5)->format('YmdHis'),
            ]);
    });

    it('narrows to the last 24 hours when asked', function (): void {
        livewire(StationH24::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->loadTable()
            ->filterTable('period', '24')
            ->assertCanSeeTableRecords([
                now()->subHour()->format('YmdHis'),
                now()->subHours(5)->format('YmdHis'),
            ])
            ->assertCanNotSeeTableRecords([
                now()->subDays(2)->format('YmdHis'),
            ]);
    });

    /**
     * The windows stop at six days because that is all /belaqi holds; a longer one would have
     * returned exactly the same rows as the widest.
     */
    it('offers windows from 24 hours to six days and nothing longer', function (): void {
        $filter = livewire(StationH24::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->loadTable()
            ->instance()
            ->getTable()
            ->getFilter('period');

        // PHP normalises the numeric string keys of the options array to integers.
        expect(array_keys($filter->getOptions()))->toBe([24, 48, 72, 144]);
    });

    /**
     * /belaqi holds the last thousand readings of the whole network and takes no date range,
     * so a long window cannot reach further back than the API itself does. Rather than let a
     * month look identical to a week, the table says where the history actually starts.
     */
    it('says how far back the history really goes when the widest period is asked for', function (): void {
        livewire(StationH24::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->loadTable()
            ->filterTable('period', '144')
            ->assertSee("L'API ISSEP ne conserve que les 1000 derniers relevés du réseau");
    });

    it('says nothing about coverage when the period is inside the history', function (): void {
        livewire(StationH24::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->loadTable()
            ->filterTable('period', '24')
            ->assertDontSee("L'API ISSEP ne conserve que");
    });

    it('reaches the oldest readings on the widest period', function (): void {
        livewire(StationH24::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->loadTable()
            ->filterTable('period', '144')
            ->assertCanSeeTableRecords([
                now()->subDays(5)->format('YmdHis'),
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
