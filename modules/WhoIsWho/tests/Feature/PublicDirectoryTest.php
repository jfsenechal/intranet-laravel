<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Service;
use AcMarche\WhoIsWho\Filament\Pages\Favorites;
use AcMarche\WhoIsWho\Filament\Pages\Index;
use AcMarche\WhoIsWho\Filament\Pages\Search;
use AcMarche\WhoIsWho\Filament\Pages\Services;
use AcMarche\WhoIsWho\Filament\Resources\Employees\EmployeeResource;
use App\Models\User;
use Filament\Facades\Filament;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('who-is-who-panel'));
});

/**
 * Directory entries need an active contract to show up at all.
 *
 * @param  array<string, mixed>  $attributes
 * @param  array<string, mixed>  $contractAttributes
 */
function publicDirectoryAgent(array $attributes = [], array $contractAttributes = []): Employee
{
    $employee = Employee::factory()->create([
        'status' => StatusEnum::AGENT->value,
        'is_archived' => false,
        ...$attributes,
    ]);

    Contract::factory()->create([
        'employee_id' => $employee->id,
        'is_closed' => false,
        'is_suspended' => false,
        'end_date' => null,
        ...$contractAttributes,
    ]);

    return $employee;
}

it('lets a guest browse the directory pages', function (string $url): void {
    publicDirectoryAgent();

    auth()->logout();
    $this->assertGuest();

    $this->get($url)->assertOk();
})->with([
    'index' => fn (): string => Index::getUrl(),
    'search' => fn (): string => Search::getUrl(),
    'services' => fn (): string => Services::getUrl(),
]);

it('shows a guest the agents of the A → Z directory', function (): void {
    $service = Service::factory()->create(['name' => 'Service informatique']);

    $employee = publicDirectoryAgent(
        ['last_name' => 'Dupont', 'first_name' => 'Marie'],
        ['job_title' => 'Analyste programmeur', 'service_id' => $service->id],
    );

    auth()->logout();

    $this->get(Index::getUrl())
        ->assertOk()
        ->assertSee('Dupont')
        ->assertSee('Marie')
        ->assertSee('Analyste programmeur')
        ->assertSee('Service informatique')
        ->assertSeeHtml(EmployeeResource::getUrl('view', ['record' => $employee]));
});

it('shows a guest the professional contact details of an agent', function (): void {
    $employee = publicDirectoryAgent([
        'professional_email' => 'marie.dupont@marche.be',
        'professional_phone' => '084 32 70 00',
        'professional_phone_extension' => '214',
        'professional_mobile' => '0470 12 34 56',
    ]);

    auth()->logout();

    $this->get(EmployeeResource::getUrl('view', ['record' => $employee]))
        ->assertOk()
        ->assertSee($employee->last_name)
        ->assertSee('marie.dupont@marche.be')
        ->assertSee('084 32 70 00 (ext. 214)')
        ->assertSee('0470 12 34 56');
});

it('keeps hiding private contact details and HR data from a guest', function (): void {
    $employee = publicDirectoryAgent([
        'private_email' => 'secret@gmail.com',
        'private_phone' => '0499999999',
        'national_registry_number' => '85073003328',
        'address' => 'Rue Secrète 12',
        'notes' => 'Remarque RH confidentielle',
    ]);

    auth()->logout();

    $this->get(EmployeeResource::getUrl('view', ['record' => $employee]))
        ->assertOk()
        ->assertDontSee('secret@gmail.com')
        ->assertDontSee('0499999999')
        ->assertDontSee('85073003328')
        ->assertDontSee('Rue Secrète 12')
        ->assertDontSee('Remarque RH confidentielle');
});

it('offers no favorite toggle to a guest', function (): void {
    $employee = publicDirectoryAgent();

    auth()->logout();

    $this->get(Index::getUrl())
        ->assertOk()
        ->assertDontSee('toggleFavoriteEmployee')
        ->assertDontSee('Ajouter aux favoris');

    $this->get(EmployeeResource::getUrl('view', ['record' => $employee]))
        ->assertOk()
        ->assertDontSee('Ajouter à mes favoris');
});

it('hides the favorites page from a guest', function (): void {
    auth()->logout();

    $this->get(Favorites::getUrl())
        ->assertForbidden();

    $this->get(Index::getUrl())
        ->assertOk()
        ->assertDontSee(Favorites::getNavigationLabel());
});

it('still offers the favorite toggle and the favorites page to a signed in user', function (): void {
    $employee = publicDirectoryAgent();

    $this->actingAs(User::factory()->create(['is_administrator' => false]));

    $this->get(Index::getUrl())
        ->assertOk()
        ->assertSee('toggleFavoriteEmployee')
        ->assertSee(Favorites::getNavigationLabel());

    $this->get(EmployeeResource::getUrl('view', ['record' => $employee]))
        ->assertOk()
        ->assertSee('Ajouter à mes favoris');

    $this->get(Favorites::getUrl())->assertOk();
});
