<?php

declare(strict_types=1);

use AcMarche\Issep\Enums\RolesEnum;
use AcMarche\Issep\Filament\Pages\StationSearch;
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
    it('renders', function (): void {
        livewire(StationSearch::class)
            ->assertSuccessful()
            ->assertSee('Recherche de données brutes');
    });

    it('offers every station of the network, sorted by name', function (): void {
        livewire(StationSearch::class)
            ->assertSuccessful()
            ->assertSee('Avenue de France (12)')
            ->assertSee('Chaussée de Liège (1)');
    });

    it('defaults to the first station so the page opens with data', function (): void {
        livewire(StationSearch::class)
            ->loadTable()
            ->assertCanSeeTableRecords([
                now()->subHour()->format('YmdHis'),
                now()->subHours(2)->format('YmdHis'),
            ]);
    });
});

describe('measurements', function (): void {
    beforeEach(function (): void {
        $this->component = livewire(StationSearch::class)
            ->loadTable()
            ->filterTable('station', IssepApiFake::STATION_WITH_READING);
    });

    it('shows the newest measurement first', function (): void {
        $this->component->assertCanSeeTableRecords([
            now()->subHour()->format('YmdHis'),
            now()->subHours(2)->format('YmdHis'),
        ], inOrder: true);
    });

    /**
     * This endpoint sends "ppbNo" and "mWhBat" where /lastdata sends "ppbno" and "mwhBat", so
     * the columns address a normalised key rather than the spelling of one endpoint.
     */
    it('reads a measurement whatever spelling the endpoint used', function (): void {
        $this->component
            ->assertTableColumnStateSet('ppbno', 1.402, now()->subHour()->format('YmdHis'))
            ->assertTableColumnStateSet('mwhbat', 3.3, now()->subHour()->format('YmdHis'))
            ->assertTableColumnStateSet('bmet', 22.4, now()->subHour()->format('YmdHis'))
            ->assertTableColumnStateSet('pm25', 9.874, now()->subHour()->format('YmdHis'));
    });

    it('shows the timestamp on the Belgian clock', function (): void {
        $this->component->assertTableColumnFormattedStateSet(
            'moment',
            now()->subHour()->setTimezone(IssepApiFake::DISPLAY_TIMEZONE)->format('d/m/Y H:i:s'),
            now()->subHour()->format('YmdHis'),
        );
    });

    /**
     * Forty fields would be unreadable, so the battery telemetry and the status codes start
     * toggled off. This asserts the toggle state rather than visibility: a toggled-off column
     * is not `hidden`, it is simply not selected.
     */
    it('keeps the secondary measurements behind the column manager', function (): void {
        $table = $this->component->instance()->getTable();

        expect($table->getColumn('pm25')->isToggledHiddenByDefault())->toBeFalse()
            ->and($table->getColumn('bmet')->isToggledHiddenByDefault())->toBeFalse()
            ->and($table->getColumn('vbat')->isToggledHiddenByDefault())->toBeTrue()
            ->and($table->getColumn('ppbnostatut')->isToggledHiddenByDefault())->toBeTrue();
    });
});

describe('the date range', function (): void {
    /**
     * The API ends its range exclusively, so asking for a single day would return nothing
     * without the repository adding a day. This pins the request the module actually sends.
     */
    it('asks the API for the day after the chosen end date', function (): void {
        livewire(StationSearch::class)
            ->loadTable()
            ->filterTable('station', IssepApiFake::STATION_WITH_READING)
            ->filterTable('period', [
                'dateBegin' => '2026-08-16',
                'dateEnd' => '2026-08-16',
            ]);

        Http::assertSent(fn (Illuminate\Http\Client\Request $request): bool => str_contains(
            $request->url(),
            '/config/'.IssepApiFake::CONFIG_WITH_READING.'/start/2026-08-16/end/2026-08-17',
        ));
    });

    /**
     * A day is about 1800 rows, so an unbounded range would download and cache tens of
     * megabytes. The clamp keeps the most recent days.
     */
    it('clamps a range wider than the configured maximum', function (): void {
        config()->set('issep.max_range_days', 3);

        livewire(StationSearch::class)
            ->loadTable()
            ->filterTable('station', IssepApiFake::STATION_WITH_READING)
            ->filterTable('period', [
                'dateBegin' => '2026-01-01',
                'dateEnd' => '2026-08-16',
            ])
            ->assertNotified();

        Http::assertSent(fn (Illuminate\Http\Client\Request $request): bool => str_contains(
            $request->url(),
            '/start/2026-08-14/end/2026-08-17',
        ));
    });

    it('refuses a range that ends before it begins', function (): void {
        livewire(StationSearch::class)
            ->loadTable()
            ->filterTable('station', IssepApiFake::STATION_WITH_READING)
            ->filterTable('period', [
                'dateBegin' => '2026-08-16',
                'dateEnd' => '2026-08-10',
            ])
            ->assertNotified()
            ->assertCanNotSeeTableRecords([now()->subHour()->format('YmdHis')]);
    });
});

describe('api failures', function (): void {
    it('reports a failure of the measurement endpoint', function (): void {
        IssepApiFake::fake(['config' => Http::response(status: 500)]);

        livewire(StationSearch::class)
            ->assertSuccessful()
            ->loadTable()
            ->assertNotified();
    });

    it('renders without a station list', function (): void {
        IssepApiFake::fake(['locations' => Http::response(status: 500)]);

        livewire(StationSearch::class)
            ->assertSuccessful()
            ->loadTable()
            ->assertCanNotSeeTableRecords([now()->subHour()->format('YmdHis')]);
    });
});

describe('authorization', function (): void {
    it('denies the page to a user without ROLE_CAPTEUR', function (): void {
        $this->actingAs(User::factory()->create(['is_administrator' => false]));

        $this->get(StationSearch::getUrl())->assertForbidden();
    });
});
