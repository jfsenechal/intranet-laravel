<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\DayTypeEnum;
use AcMarche\Hrm\Enums\LocationTypeEnum;
use AcMarche\Hrm\Enums\RolesEnum;
use AcMarche\Hrm\Filament\Exports\TeleworkExport;
use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\EditTelework;
use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\HrValidateTelework;
use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\ListTeleworks;
use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\ManagerValidateTelework;
use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\ViewTelework;
use AcMarche\Hrm\Mail\TeleworkManagerValidationMail;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Direction;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Telework;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
    $hrmAdminRole = Role::factory()->create(['name' => RolesEnum::ROLE_GRH_ADMIN->value]);
    $this->adminUser = User::factory()->create(['is_administrator' => true]);
    $this->adminUser->roles()->attach($hrmAdminRole);
    $this->actingAs($this->adminUser);
});

/**
 * Gives the acting user an Employee record attached to an active contract, so the
 * request resolves to a direction. Telework stamps `user_add` from the authenticated
 * user via HasUserAdd, hence the shared username.
 */
/**
 * `professional_email` is intentionally not mass-assignable on Employee, so it is
 * set outside the factory attributes.
 */
function directorNamed(string $username, ?string $email): Employee
{
    $employee = Employee::factory()->create([
        'username' => $username,
        'last_name' => 'WILMET',
        'first_name' => 'Quentin',
    ]);

    $employee->professional_email = $email;
    $employee->save();

    return $employee;
}

function requesterWithDirector(string $adminUsername, ?string $directorUsername): Employee
{
    $employee = Employee::factory()->create(['username' => $adminUsername]);

    $direction = Direction::factory()->create([
        'name' => 'Direction du Personnel',
        'director' => $directorUsername,
    ]);

    Contract::factory()->create([
        'employee_id' => $employee->getKey(),
        'direction_id' => $direction->getKey(),
        'is_closed' => false,
        'is_suspended' => false,
        'end_date' => null,
    ]);

    return $employee;
}

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListTeleworks::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = Telework::factory()->create();

        Livewire::test(ViewTelework::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Telework::factory()->create();

        Livewire::test(EditTelework::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the manager validate page', function (): void {
        $record = Telework::factory()->create();

        Livewire::test(ManagerValidateTelework::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the hr validate page', function (): void {
        $record = Telework::factory()->create();

        Livewire::test(HrValidateTelework::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });
});

describe('director who must validate', function (): void {
    it('shows the direction and the director who must validate the request', function (): void {
        $director = Employee::factory()->create([
            'username' => 'qwilmet',
            'last_name' => 'WILMET',
            'first_name' => 'Quentin',
        ]);
        $director->professional_email = 'quentin.wilmet@marche.be';
        $director->save();

        requesterWithDirector($this->adminUser->username, 'qwilmet');

        $record = Telework::factory()->create();

        Livewire::test(ViewTelework::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSee('Directeur qui doit valider')
            ->assertSee('Direction du Personnel')
            ->assertSee('WILMET Quentin')
            ->assertSee('quentin.wilmet@marche.be')
            ->assertDontSee('la demande de validation ne lui a pas');
    });

    it('warns when the direction has no director', function (): void {
        requesterWithDirector($this->adminUser->username, null);

        $record = Telework::factory()->create();

        Livewire::test(ViewTelework::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSee('Aucun directeur renseigné')
            ->assertSee('la demande de validation ne lui a pas été envoyée par mail.');
    });

    it('warns when the director has no email address', function (): void {
        directorNamed('qwilmet', null);
        requesterWithDirector($this->adminUser->username, 'qwilmet');

        $record = Telework::factory()->create();

        Livewire::test(ViewTelework::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSee('WILMET Quentin')
            ->assertSee('la demande de validation ne lui a pas été envoyée par mail.');
    });

    it('warns when the agent has no active contract', function (): void {
        Employee::factory()->create(['username' => $this->adminUser->username]);

        $record = Telework::factory()->create();

        Livewire::test(ViewTelework::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSee('Aucun contrat actif')
            ->assertSee('la demande de validation ne lui a pas été envoyée par mail.');
    });
});

describe('director access to the validation page', function (): void {
    /**
     * A director holds ROLE_GRH_DIRECTION and no GRH administration role: their access
     * comes solely from being the director of the requester's direction.
     */
    function actingAsDirector(string $username): User
    {
        $role = Role::firstOrCreate(['name' => RolesEnum::ROLE_GRH_DIRECTION->value]);
        $user = User::factory()->create(['username' => $username]);
        $user->addRole($role);

        test()->actingAs($user);

        return $user;
    }

    /**
     * Telework stamps `user_add` from the authenticated user, so the request is created
     * while acting as the requester before switching to the director.
     */
    function requestFromAgentOfDirection(string $agentUsername, ?string $directorUsername): Telework
    {
        $employee = Employee::factory()->create(['username' => $agentUsername]);

        $direction = Direction::factory()->create(['director' => $directorUsername]);

        Contract::factory()->create([
            'employee_id' => $employee->getKey(),
            'direction_id' => $direction->getKey(),
            'is_closed' => false,
            'is_suspended' => false,
            'end_date' => null,
        ]);

        test()->actingAs(User::factory()->create(['username' => $agentUsername]));

        return Telework::factory()->create();
    }

    it('lets the director of the requester validate the request', function (): void {
        $record = requestFromAgentOfDirection('jdoe', 'qwilmet');
        actingAsDirector('qwilmet');

        $this->get(ManagerValidateTelework::getUrl(['record' => $record], panel: 'hrm-panel'))
            ->assertOk();
    });

    it('lets the director of the requester open the request', function (): void {
        $record = requestFromAgentOfDirection('jdoe', 'qwilmet');
        actingAsDirector('qwilmet');

        $this->get(ViewTelework::getUrl(['record' => $record], panel: 'hrm-panel'))
            ->assertOk();
    });

    it('lets the director record their decision', function (): void {
        $record = requestFromAgentOfDirection('jdoe', 'qwilmet');
        actingAsDirector('qwilmet');

        Livewire::test(ManagerValidateTelework::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'manager_validated' => true,
                'manager_validator_name' => 'WILMET Quentin',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($record->refresh()->manager_validated)->toBeTrue();
    });

    /**
     * The resource query hides records outside the user's direction, so an unauthorized
     * user cannot resolve the route binding at all and gets a 404 rather than a 403.
     */
    it('hides the request from a director of another direction', function (): void {
        $record = requestFromAgentOfDirection('jdoe', 'qwilmet');
        actingAsDirector('someone-else');

        $this->get(ManagerValidateTelework::getUrl(['record' => $record], panel: 'hrm-panel'))
            ->assertNotFound();
    });

    it('hides the request from an agent without any director role', function (): void {
        $record = requestFromAgentOfDirection('jdoe', 'qwilmet');

        $this->actingAs(User::factory()->create(['username' => 'nobody']));

        $this->get(ManagerValidateTelework::getUrl(['record' => $record], panel: 'hrm-panel'))
            ->assertNotFound();
    });

    it('keeps the full edit form closed to the director', function (): void {
        $record = requestFromAgentOfDirection('jdoe', 'qwilmet');
        actingAsDirector('qwilmet');

        $this->get(EditTelework::getUrl(['record' => $record], panel: 'hrm-panel'))
            ->assertForbidden();
    });

    it('lists only the requests of the director own agents', function (): void {
        $own = requestFromAgentOfDirection('jdoe', 'qwilmet');
        $other = requestFromAgentOfDirection('asmith', 'another-director');

        actingAsDirector('qwilmet');

        Livewire::test(ListTeleworks::class)
            ->loadTable()
            ->assertCanSeeTableRecords([$own])
            ->assertCanNotSeeTableRecords([$other]);
    });

    it('still lists every request for a grh administrator', function (): void {
        $first = requestFromAgentOfDirection('jdoe', 'qwilmet');
        $second = requestFromAgentOfDirection('asmith', 'another-director');

        $this->actingAs($this->adminUser);

        Livewire::test(ListTeleworks::class)
            ->loadTable()
            ->assertCanSeeTableRecords([$first, $second]);
    });
});

describe('validation request mail', function (): void {
    it('sends the request to the director with a link to the validation page', function (): void {
        Mail::fake();

        directorNamed('qwilmet', 'quentin.wilmet@marche.be');
        requesterWithDirector($this->adminUser->username, 'qwilmet');

        $record = Telework::factory()->create();

        Livewire::test(ViewTelework::class, [
            'record' => $record->id,
        ])
            ->assertActionEnabled('requestManagerValidation')
            ->callAction('requestManagerValidation')
            ->assertNotified('Demande de validation envoyée');

        Mail::assertQueued(
            TeleworkManagerValidationMail::class,
            fn (TeleworkManagerValidationMail $mail): bool => $mail->hasTo('quentin.wilmet@marche.be')
                && str_contains(
                    $mail->render(),
                    ManagerValidateTelework::getUrl(['record' => $record], panel: 'hrm-panel'),
                ),
        );
    });

    it('reports that nothing was sent when no director is resolvable', function (): void {
        Mail::fake();

        $record = Telework::factory()->create();

        Livewire::test(ViewTelework::class, [
            'record' => $record->id,
        ])
            ->assertActionDisabled('requestManagerValidation')
            ->call('requestManagerValidation')
            ->assertNotified('Aucun mail envoyé');

        Mail::assertNotQueued(TeleworkManagerValidationMail::class);
    });

    it('reports that nothing was sent when the director has no email', function (): void {
        Mail::fake();

        directorNamed('qwilmet', null);
        requesterWithDirector($this->adminUser->username, 'qwilmet');

        $record = Telework::factory()->create();

        Livewire::test(ViewTelework::class, [
            'record' => $record->id,
        ])
            ->assertActionDisabled('requestManagerValidation')
            ->call('requestManagerValidation')
            ->assertNotified('Aucun mail envoyé');

        Mail::assertNotQueued(TeleworkManagerValidationMail::class);
    });

    it('hides the button once the director has decided', function (): void {
        directorNamed('qwilmet', 'quentin.wilmet@marche.be');
        requesterWithDirector($this->adminUser->username, 'qwilmet');

        $record = Telework::factory()->create(['manager_validated' => true]);

        Livewire::test(ViewTelework::class, [
            'record' => $record->id,
        ])
            ->assertActionHidden('requestManagerValidation');
    });
});

describe('model behavior', function (): void {
    it('automatically generates a uuid on creation', function (): void {
        $telework = Telework::factory()->create();

        expect($telework->uuid)
            ->not->toBeNull()
            ->toBeString();
    });

    it('casts location_type to LocationTypeEnum', function (): void {
        $telework = Telework::factory()->create([
            'location_type' => LocationTypeEnum::Domicile->value,
        ]);

        expect($telework->location_type)->toBe(LocationTypeEnum::Domicile);
    });

    it('casts day_type to DayTypeEnum', function (): void {
        $telework = Telework::factory()->create([
            'day_type' => DayTypeEnum::Fixe->value,
        ]);

        expect($telework->day_type)->toBe(DayTypeEnum::Fixe);
    });

    it('casts agreements as boolean', function (): void {
        $telework = Telework::factory()->create([
            'regulation_agreement' => true,
            'it_agreement' => true,
        ]);

        expect($telework->regulation_agreement)->toBeTrue();
        expect($telework->it_agreement)->toBeTrue();
    });
});

describe('export action', function (): void {
    it('renders the export action on the index page', function (): void {
        Livewire::test(ListTeleworks::class)
            ->assertActionExists('export');
    });

    it('can trigger the export action with all columns', function (): void {
        Telework::factory(2)->create();

        Livewire::test(ListTeleworks::class)
            ->callAction('export', data: ['columns' => array_keys(TeleworkExport::columns())])
            ->assertHasNoActionErrors();
    });

    it('can trigger the export action with a subset of columns', function (): void {
        Telework::factory(2)->create();

        Livewire::test(ListTeleworks::class)
            ->callAction('export', data: ['columns' => ['user_add', 'full_name', 'created_at']])
            ->assertHasNoActionErrors();
    });

    it('requires at least one column to be selected', function (): void {
        Livewire::test(ListTeleworks::class)
            ->callAction('export', data: ['columns' => []])
            ->assertHasActionErrors(['columns']);
    });
});
