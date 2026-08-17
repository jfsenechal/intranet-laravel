<?php

declare(strict_types=1);

use AcMarche\Issep\Enums\RolesEnum;
use AcMarche\Issep\Filament\Pages\StationConfig;
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
        livewire(StationConfig::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->assertSuccessful()
            ->assertSee('Configuration de Chaussée de Liège (1)');
    });

    it('shows the identity of the station', function (): void {
        livewire(StationConfig::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->assertSee((string) IssepApiFake::CONFIG_WITH_READING)
            ->assertSee('50.226024');
    });

    it('404s on a station the network does not have', function (): void {
        livewire(StationConfig::class, ['station' => 99999])->assertNotFound();
    });
});

describe('measurement fields', function (): void {
    it('lists the fields the sensor reported', function (): void {
        livewire(StationConfig::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->loadTable()
            ->assertCanSeeTableRecords(['ppbno', 'bmeT', 'pm25', 'vbat']);
    });

    it('shows the value of a field', function (): void {
        livewire(StationConfig::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->loadTable()
            ->assertTableColumnStateSet('value', '21.5', 'bmeT');
    });

    /**
     * The API is not consistent in how it spells a field, so the description is looked up on
     * a normalised key: /lastdata's "bmeT" must find the same "température" as "BME_t" would.
     */
    it('describes a field whatever its spelling', function (): void {
        livewire(StationConfig::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->loadTable()
            ->assertTableColumnStateSet('description', 'Température exprimée en °C', 'bmeT')
            ->assertTableColumnStateSet(
                'description',
                'Statut de la mesure: concentration en NO exprimée en ppb (parts par milliard)',
                'ppbnoStatut',
            );
    });

    it('finds a field by name', function (): void {
        livewire(StationConfig::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->loadTable()
            ->searchTable('pm25')
            ->assertCanSeeTableRecords(['pm25'])
            ->assertCanNotSeeTableRecords(['vbat']);
    });

    it('reports a failure of the measurement endpoint', function (): void {
        IssepApiFake::fake(['lastdata' => Http::response(status: 500)]);

        livewire(StationConfig::class, ['station' => IssepApiFake::STATION_WITH_READING])
            ->assertSuccessful()
            ->loadTable()
            ->assertNotified();
    });
});
