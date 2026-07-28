<?php

declare(strict_types=1);

use AcMarche\App\Filament\Pages\TeleworkPage;
use AcMarche\Hrm\Enums\DayTypeEnum;
use AcMarche\Hrm\Enums\LocationTypeEnum;
use AcMarche\Hrm\Mail\TeleworkEmployeeSubmittedMail;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Telework;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('app-panel'));
    $this->user = User::factory()->create(['username' => 'mmartin']);
    $this->actingAs($this->user);
});

it('renders the telework page', function (): void {
    Livewire::test(TeleworkPage::class)->assertOk();
});

it('hides the validation follow-up when no request exists', function (): void {
    Livewire::test(TeleworkPage::class)
        ->assertOk()
        ->assertDontSee('Suivi de ma demande');
});

it('shows the pending validation follow-up when a request exists', function (): void {
    Telework::factory()->create(['user_add' => 'mmartin']);

    Livewire::test(TeleworkPage::class)
        ->assertOk()
        ->assertSee('Suivi de ma demande')
        ->assertSee('En attente de validation par la direction de service')
        ->assertSee('Validation par la direction de service')
        ->assertSee('Traitement par le service GRH');
});

it('shows the manager decision and the hr processing in the follow-up', function (): void {
    Telework::factory()->create([
        'user_add' => 'mmartin',
        'manager_validated' => true,
        'manager_validated_at' => '2026-06-15',
        'manager_validator_name' => 'Alice Dupont',
        'date_college' => '2026-07-01',
        'hr_validator_name' => 'Bob Lambert',
    ]);

    Livewire::test(TeleworkPage::class)
        ->assertOk()
        ->assertSee('Traitée par le service GRH')
        ->assertSee('15/06/2026')
        ->assertSee('Alice Dupont')
        ->assertSee('01/07/2026')
        ->assertSee('Bob Lambert');
});

it('shows a refusal in the follow-up', function (): void {
    Telework::factory()->create([
        'user_add' => 'mmartin',
        'manager_validated' => false,
    ]);

    Livewire::test(TeleworkPage::class)
        ->assertOk()
        ->assertSee('Refusée par la direction de service');
});

it('creates a telework request with the address on save', function (): void {
    Livewire::test(TeleworkPage::class)
        ->fillForm([
            'street' => 'Rue de la Belle Eau 37',
            'postal_code' => '6640',
            'locality' => 'Vaux-sur-Sûre',
            'location_type' => LocationTypeEnum::Domicile->value,
            'day_type' => DayTypeEnum::Variable->value,
            'variable_day_reason' => '<p>Jour variable en fonction des nécessités du service</p>',
            'regulation_agreement' => true,
            'it_agreement' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Telework::class, [
        'user_add' => 'mmartin',
        'street' => 'Rue de la Belle Eau 37',
        'postal_code' => '6640',
        'locality' => 'Vaux-sur-Sûre',
    ]);
});

it('confirms the submission to the requester by email', function (): void {
    Mail::fake();

    $employee = Employee::factory()->create(['username' => 'mmartin']);
    $employee->professional_email = 'mmartin@marche.be';
    $employee->save();

    Livewire::test(TeleworkPage::class)
        ->fillForm([
            'street' => 'Rue de la Belle Eau 37',
            'postal_code' => '6640',
            'locality' => 'Vaux-sur-Sûre',
            'location_type' => LocationTypeEnum::Domicile->value,
            'day_type' => DayTypeEnum::Variable->value,
            'variable_day_reason' => '<p>Jour variable</p>',
            'regulation_agreement' => true,
            'it_agreement' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    Mail::assertQueued(
        TeleworkEmployeeSubmittedMail::class,
        fn (TeleworkEmployeeSubmittedMail $mail): bool => $mail->hasTo('mmartin@marche.be'),
    );
});

it('does not re-confirm when an existing request is updated', function (): void {
    Mail::fake();

    $employee = Employee::factory()->create(['username' => 'mmartin']);
    $employee->professional_email = 'mmartin@marche.be';
    $employee->save();

    Telework::factory()->create(['user_add' => 'mmartin', 'postal_code' => '6900']);

    Livewire::test(TeleworkPage::class)
        ->fillForm(['postal_code' => '6640'])
        ->call('save')
        ->assertHasNoFormErrors();

    Mail::assertNotQueued(TeleworkEmployeeSubmittedMail::class);
});

it('updates the existing request instead of creating a second one', function (): void {
    Telework::factory()->create([
        'user_add' => 'mmartin',
        'postal_code' => '6900',
    ]);

    Livewire::test(TeleworkPage::class)
        ->fillForm(['postal_code' => '6640'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Telework::query()->where('user_add', 'mmartin')->count())->toBe(1);

    assertDatabaseHas(Telework::class, [
        'user_add' => 'mmartin',
        'postal_code' => '6640',
    ]);
});
