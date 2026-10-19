<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Filament\Resources\Declarations\DeclarationResource;
use AcMarche\Mileage\Filament\Resources\Trips\Pages\CreateTrip;
use AcMarche\Mileage\Filament\Resources\Trips\Pages\EditTrip;
use AcMarche\Mileage\Filament\Resources\Trips\Pages\ListTrips;
use AcMarche\Mileage\Filament\Resources\Trips\Pages\ViewTrip;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\PersonalInformation;
use AcMarche\Mileage\Models\Trip;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('mileage-panel'));
    $this->user = User::factory()->create(['username' => 'jdupont', 'is_administrator' => true]);
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
    $this->user->roles()->attach($role);
    PersonalInformation::factory()->create(['username' => 'jdupont']);
    $this->actingAs($this->user);
});

it('can render the index page', function (): void {
    livewire(ListTrips::class)
        ->assertOk();
});

it('can render the create page', function (): void {
    livewire(CreateTrip::class)
        ->assertOk();
});

it('can render the view page', function (): void {
    $trip = Trip::factory()->create(['user_add' => 'jdupont']);

    livewire(ViewTrip::class, ['record' => $trip->id])
        ->assertOk();
});

it('can list trips', function (): void {
    $trips = Trip::factory(3)->create(['user_add' => 'jdupont']);

    livewire(ListTrips::class)
        ->loadTable()
        ->assertCanSeeTableRecords($trips);
});

it('has table columns', function (string $column): void {
    livewire(ListTrips::class)
        ->assertTableColumnExists($column);
})->with(['departure_date', 'distance', 'rate', 'amount']);

it('displays the trip amount and total summary', function (): void {
    $trips = collect([
        Trip::factory()->create(['user_add' => 'jdupont', 'distance' => 10, 'rate' => 0.40, 'omnium' => 0.03]),
        Trip::factory()->create(['user_add' => 'jdupont', 'distance' => 25, 'rate' => 0.40, 'omnium' => 0.03]),
    ]);

    $expectedTotal = $trips->sum(fn (Trip $trip): float => $trip->distance * ((float) $trip->rate - (float) $trip->omnium));

    livewire(ListTrips::class)
        ->loadTable()
        ->assertCanSeeTableRecords($trips)
        ->assertTableColumnSummarySet('amount', 'total', $expectedTotal);
});

it('can sort column', function (string $column): void {
    $trips = Trip::factory(5)->create(['user_add' => 'jdupont']);

    livewire(ListTrips::class)
        ->loadTable()
        ->sortTable($column)
        ->assertCanSeeTableRecords($trips->sortBy($column), inOrder: true)
        ->sortTable($column, 'desc')
        ->assertCanSeeTableRecords($trips->sortByDesc($column), inOrder: true);
})->with(['departure_date', 'distance']);

it('can create a trip', function (): void {
    $trip = Trip::factory()->make(['user_add' => 'jdupont']);

    livewire(CreateTrip::class)
        ->fillForm([
            'distance' => $trip->distance,
            'departure_date' => $trip->departure_date,
            'content' => $trip->content,
        ])
        ->call('create')
        ->assertNotified();

    assertDatabaseHas(Trip::class, [
        'distance' => $trip->distance,
        'content' => $trip->content,
    ]);
});

it('can update a trip not attached to a declaration', function (): void {
    $trip = Trip::factory()->create(['user_add' => 'jdupont', 'declaration_id' => null]);

    livewire(EditTrip::class, ['record' => $trip->id])
        ->fillForm(['content' => 'Updated content'])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Trip::class, [
        'id' => $trip->id,
        'content' => 'Updated content',
    ]);
});

it('forbids the owner to edit a trip attached to a declaration', function (): void {
    actingAsTripOwner();
    $declaration = Declaration::factory()->create();
    $trip = Trip::factory()->create(['declaration_id' => $declaration->id]);

    livewire(EditTrip::class, ['record' => $trip->id])
        ->assertForbidden();
});

it('lets an administrator edit a trip attached to a declaration', function (): void {
    $declaration = Declaration::factory()->create();
    $trip = Trip::factory()->create(['user_add' => 'jdupont', 'declaration_id' => $declaration->id]);

    livewire(EditTrip::class, ['record' => $trip->id])
        ->assertOk();
});

it('shows the owner the edit table action only on trips not attached to a declaration', function (): void {
    actingAsTripOwner();
    $declaration = Declaration::factory()->create();
    $undeclaredTrip = Trip::factory()->create(['declaration_id' => null]);
    $declaredTrip = Trip::factory()->create(['declaration_id' => $declaration->id]);

    livewire(ListTrips::class)
        ->filterTable('declared', null)
        ->loadTable()
        ->assertActionVisible(TestAction::make('edit')->table($undeclaredTrip))
        ->assertActionHidden(TestAction::make('edit')->table($declaredTrip));
});

it('explains on the view page that a declared trip is reserved to administrators', function (): void {
    $declaration = Declaration::factory()->create();
    $declaredTrip = Trip::factory()->create(['user_add' => 'jdupont', 'declaration_id' => $declaration->id]);
    $undeclaredTrip = Trip::factory()->create(['user_add' => 'jdupont', 'declaration_id' => null]);

    livewire(ViewTrip::class, ['record' => $declaredTrip->id])
        ->assertSee('seul un administrateur peut encore le modifier ou le supprimer');

    livewire(ViewTrip::class, ['record' => $undeclaredTrip->id])
        ->assertDontSee('seul un administrateur peut encore le modifier ou le supprimer');
});

it('stores the meal expense entered in the form', function (): void {
    $trip = Trip::factory()->make(['user_add' => 'jdupont']);

    livewire(CreateTrip::class)
        ->fillForm([
            'distance' => $trip->distance,
            'departure_date' => $trip->departure_date,
            'content' => $trip->content,
            'meal_expense' => 12.30,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Trip::class, [
        'content' => $trip->content,
        'meal_expense' => 12.30,
    ]);
});

it('links a declared trip to its declaration on the view page', function (): void {
    $declaration = Declaration::factory()->create(['user_add' => 'jdupont']);
    $declaredTrip = Trip::factory()->create(['user_add' => 'jdupont', 'declaration_id' => $declaration->id]);
    $undeclaredTrip = Trip::factory()->create(['user_add' => 'jdupont', 'declaration_id' => null]);

    livewire(ViewTrip::class, ['record' => $declaredTrip->id])
        ->assertActionVisible('viewDeclaration')
        ->assertActionHasUrl('viewDeclaration', DeclarationResource::getUrl('view', ['record' => $declaration]));

    livewire(ViewTrip::class, ['record' => $undeclaredTrip->id])
        ->assertActionHidden('viewDeclaration');
});

it('validates the form data', function (array $data, array $errors): void {
    $trip = Trip::factory()->make(['user_add' => 'jdupont']);

    livewire(CreateTrip::class)
        ->fillForm([
            'distance' => $trip->distance,
            'departure_date' => $trip->departure_date,
            'content' => $trip->content,
            ...$data,
        ])
        ->call('create')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`distance` is required' => [['distance' => null], ['distance' => 'required']],
    '`departure_date` is required' => [['departure_date' => null], ['departure_date' => 'required']],
    '`content` is required' => [['content' => null], ['content' => 'required']],
]);

it('prefills the trip details with the departure and arrival template', function (): void {
    livewire(CreateTrip::class)
        ->assertSchemaStateSet([
            'content' => "Lieu de départ: \nLieu d'arrivée: \nObjet: ",
        ]);
});

it('never displays the departure time inputs', function (): void {
    livewire(CreateTrip::class)
        ->assertFormFieldExists('departure_date', checkFieldUsing: fn (DateTimePicker $field): bool => ! $field->hasTime());
});

it('stores the departure day at 08h00 on the Belgian clock', function (): void {
    // The dates are a wall clock, not an instant: Filament's display timezone
    // must not move a departure encoded on 08-09 to the previous day.
    $trip = Trip::factory()->make(['user_add' => 'jdupont']);

    livewire(CreateTrip::class)
        ->fillForm([
            'distance' => $trip->distance,
            'content' => $trip->content,
            'departure_date' => '2026-09-08 00:00:00',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Trip::class, [
        'content' => $trip->content,
        'departure_date' => '2026-09-08 08:00:00',
    ]);
});

it('displays the stored departure date without shifting it', function (): void {
    // A late departure is the case that separates the two: rendered as a
    // datetime it would be pushed to the next day by the display timezone.
    $trip = Trip::factory()->create([
        'user_add' => 'jdupont',
        'departure_date' => '2026-09-08 22:30:00',
    ]);

    livewire(ListTrips::class)
        ->loadTable()
        ->assertTableColumnFormattedStateSet('departure_date', '08/09/2026', $trip);
});

it('renders the table for a non-admin owner without missing attribute error', function (): void {
    $owner = User::factory()->create(['username' => 'rhoubrechts', 'is_administrator' => false]);
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
    $owner->roles()->attach($role);
    PersonalInformation::factory()->create(['username' => 'rhoubrechts']);

    $this->actingAs($owner);

    $trips = Trip::factory(3)->create(['user_add' => 'rhoubrechts']);

    livewire(ListTrips::class)
        ->loadTable()
        ->assertOk()
        ->assertCanSeeTableRecords($trips);
});

it('can bulk delete trips', function (): void {
    $trips = Trip::factory(3)->create(['user_add' => 'jdupont']);

    livewire(ListTrips::class)
        ->loadTable()
        ->assertCanSeeTableRecords($trips)
        ->selectTableRecords($trips)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
        ->assertNotified()
        ->assertCanNotSeeTableRecords($trips);

    $trips->each(fn (Trip $trip) => assertDatabaseMissing($trip));
});

/**
 * Act as a beneficiary who is neither a global administrator nor a finance admin.
 */
function actingAsTripOwner(): User
{
    $owner = User::factory()->create(['username' => 'pmartin', 'is_administrator' => false]);
    $owner->roles()->attach(Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]));
    PersonalInformation::factory()->create(['username' => 'pmartin']);
    test()->actingAs($owner);

    return $owner;
}
