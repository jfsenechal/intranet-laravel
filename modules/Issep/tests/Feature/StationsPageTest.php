<?php

declare(strict_types=1);

use AcMarche\Issep\Enums\RolesEnum;
use AcMarche\Issep\Filament\Pages\StationConfig;
use AcMarche\Issep\Filament\Pages\StationH24;
use AcMarche\Issep\Filament\Pages\Stations;
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

describe('table', function (): void {
    it('lists the stations of the network, sorted by name', function (): void {
        livewire(Stations::class)
            ->assertSuccessful()
            ->loadTable()
            ->assertCanSeeTableRecords([
                IssepApiFake::STATION_AVENUE_FRANCE,
                IssepApiFake::STATION_WITH_READING,
                IssepApiFake::STATION_WITHOUT_READING,
            ], inOrder: true);
    });

    it('shows the label and the value of the index in one column', function (): void {
        livewire(Stations::class)
            ->loadTable()
            ->assertTableColumnStateSet('belaqi', 'Bien (3)', IssepApiFake::STATION_WITH_READING);
    });

    /**
     * The API sends UTC without an offset and Filament renders app.display_timezone, so this
     * pins that a reading is shown on the Belgian clock rather than two hours early.
     */
    it('shows the timestamp of the last reading in local time', function (): void {
        livewire(Stations::class)
            ->loadTable()
            ->assertTableColumnFormattedStateSet(
                'belaqi_ts',
                now()->subHour()->setTimezone(IssepApiFake::DISPLAY_TIMEZONE)->format('d/m/Y H:i'),
                IssepApiFake::STATION_WITH_READING,
            );
    });

    it('colours the index with the BelAQI colour of its value', function (): void {
        livewire(Stations::class)
            ->loadTable()
            ->assertSeeHtml('fi-color-belaqi-3');
    });

    it('finds a station by name', function (): void {
        livewire(Stations::class)
            ->loadTable()
            ->searchTable('France')
            ->assertCanSeeTableRecords([IssepApiFake::STATION_AVENUE_FRANCE])
            ->assertCanNotSeeTableRecords([IssepApiFake::STATION_WITH_READING]);
    });

    it('sorts by index value', function (): void {
        livewire(Stations::class)
            ->loadTable()
            ->sortTable('belaqi')
            ->assertCanSeeTableRecords([
                IssepApiFake::STATION_AVENUE_FRANCE,
                IssepApiFake::STATION_WITH_READING,
                IssepApiFake::STATION_WITHOUT_READING,
            ], inOrder: true);
    });
});

describe('fallback reading', function (): void {
    /**
     * A station whose own sensor reports nothing takes the indice of the fallback network,
     * which the legacy intranet showed as "corrigé".
     */
    it('falls back to the network indice and flags it', function (): void {
        livewire(Stations::class)
            ->loadTable()
            ->assertTableColumnStateSet('belaqi', 'Insuffisant (6)', IssepApiFake::STATION_WITHOUT_READING)
            ->assertTableColumnStateSet('is_fixed', 'Corrigé', IssepApiFake::STATION_WITHOUT_READING);
    });

    it('does not flag a station reading its own sensor', function (): void {
        livewire(Stations::class)
            ->loadTable()
            ->assertTableColumnStateSet('is_fixed', null, IssepApiFake::STATION_WITH_READING);
    });
});

describe('links', function (): void {
    it('links each station to its 24 hour readings', function (): void {
        livewire(Stations::class)
            ->loadTable()
            ->assertTableActionHasUrl(
                'h24',
                StationH24::getUrl(['station' => IssepApiFake::STATION_WITH_READING]),
                record: IssepApiFake::STATION_WITH_READING,
            );
    });

    it('links each station to its configuration', function (): void {
        livewire(Stations::class)
            ->loadTable()
            ->assertTableActionHasUrl(
                'config',
                StationConfig::getUrl(['station' => IssepApiFake::STATION_WITH_READING]),
                record: IssepApiFake::STATION_WITH_READING,
            );
    });
});

describe('api failures', function (): void {
    it('reports an expired token instead of rendering an empty page', function (): void {
        IssepApiFake::fake(['locations' => Http::response(status: 401)]);

        livewire(Stations::class)
            ->assertSuccessful()
            ->loadTable()
            ->assertCanNotSeeTableRecords([IssepApiFake::STATION_WITH_READING])
            ->assertNotified();
    });

    it('survives a station list that is not JSON', function (): void {
        IssepApiFake::fake(['locations' => Http::response('<html>proxy</html>')]);

        livewire(Stations::class)
            ->assertSuccessful()
            ->loadTable()
            ->assertNotified();
    });
});

describe('authorization', function (): void {
    it('denies the panel to a user without ROLE_CAPTEUR', function (): void {
        $this->actingAs(User::factory()->create(['is_administrator' => false]));

        $this->get(Stations::getUrl())->assertForbidden();
    });

    it('allows an administrator', function (): void {
        $this->actingAs(User::factory()->create(['is_administrator' => true]));

        $this->get(Stations::getUrl())->assertSuccessful();
    });
});
