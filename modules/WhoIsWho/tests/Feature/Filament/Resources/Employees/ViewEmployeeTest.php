<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Direction;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Service;
use AcMarche\WhoIsWho\Filament\Resources\Employees\Pages\ViewEmployee;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('who-is-who-panel'));

    // A plain staff member: no HRM role whatsoever.
    $this->user = User::factory()->create(['is_administrator' => false]);
    $this->actingAs($this->user);
});

/**
 * Directory entries need an active contract to show up at all.
 */
function activeAgent(array $attributes = [], array $contractAttributes = []): Employee
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

it('renders the infolist for an active agent', function (): void {
    $employee = activeAgent(['professional_email' => 'jean.dupont@marche.be']);

    livewire(ViewEmployee::class, ['record' => $employee->id])
        ->assertOk()
        ->assertSee($employee->last_name)
        ->assertSee($employee->first_name)
        ->assertSee('jean.dupont@marche.be');
});

it('shows the job title, service and direction of each active contract', function (): void {
    $direction = Direction::factory()->create(['name' => 'Direction générale']);
    $service = Service::factory()->create([
        'name' => 'Service informatique',
        'direction_id' => $direction->id,
    ]);

    $employee = activeAgent([], [
        'job_title' => 'Analyste programmeur',
        'service_id' => $service->id,
        'direction_id' => $direction->id,
    ]);

    livewire(ViewEmployee::class, ['record' => $employee->id])
        ->assertOk()
        ->assertSee('Analyste programmeur')
        ->assertSee('Service informatique')
        ->assertSee('Direction générale');
});

it('appends the extension to the desk phone', function (): void {
    $employee = activeAgent([
        'professional_phone' => '084 32 70 00',
        'professional_phone_extension' => '123',
    ]);

    livewire(ViewEmployee::class, ['record' => $employee->id])
        ->assertOk()
        ->assertSee('084 32 70 00 (ext. 123)');
});

it('never exposes private contact details or HR data', function (): void {
    $employee = activeAgent([
        'private_email' => 'secret@gmail.com',
        'private_phone' => '0499999999',
        'national_registry_number' => '85073003328',
        'address' => 'Rue Secrète 12',
        'notes' => 'Remarque RH confidentielle',
    ]);

    livewire(ViewEmployee::class, ['record' => $employee->id])
        ->assertOk()
        ->assertDontSee('secret@gmail.com')
        ->assertDontSee('0499999999')
        ->assertDontSee('85073003328')
        ->assertDontSee('Rue Secrète 12')
        ->assertDontSee('Remarque RH confidentielle');
});

it('shows the birthday only when the agent opted in', function (): void {
    $optedIn = activeAgent([
        'show_birthday' => true,
        'birth_date' => '1985-07-30',
    ]);

    livewire(ViewEmployee::class, ['record' => $optedIn->id])
        ->assertOk()
        ->assertSee('Anniversaire');

    $optedOut = activeAgent([
        'show_birthday' => false,
        'birth_date' => '1985-07-30',
    ]);

    livewire(ViewEmployee::class, ['record' => $optedOut->id])
        ->assertOk()
        ->assertDontSee('Anniversaire');
});

it('hides the birth year of an agent who opted in', function (): void {
    $employee = activeAgent([
        'show_birthday' => true,
        'birth_date' => '1985-07-30',
    ]);

    livewire(ViewEmployee::class, ['record' => $employee->id])
        ->assertOk()
        ->assertDontSee('1985');
});

it('does not expose an archived employee', function (): void {
    $employee = activeAgent(['is_archived' => true]);

    expect(fn () => livewire(ViewEmployee::class, ['record' => $employee->id]))
        ->toThrow(ModelNotFoundException::class);
});

it('does not expose an employee who is not an agent', function (): void {
    $employee = activeAgent(['status' => StatusEnum::APPLICATION->value]);

    expect(fn () => livewire(ViewEmployee::class, ['record' => $employee->id]))
        ->toThrow(ModelNotFoundException::class);
});

it('does not expose an employee without an active contract', function (): void {
    $employee = Employee::factory()->create([
        'status' => StatusEnum::AGENT->value,
        'is_archived' => false,
    ]);

    expect(fn () => livewire(ViewEmployee::class, ['record' => $employee->id]))
        ->toThrow(ModelNotFoundException::class);
});
