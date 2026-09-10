<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Filament\Resources\Trips\Pages\CreateTrip;
use AcMarche\Mileage\Filament\Resources\Trips\Pages\ListTrips;
use AcMarche\Mileage\Filament\Resources\Trips\Pages\ViewTrip;
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
})->with(['departure_date', 'departure_location', 'distance', 'rate', 'amount', 'type_movement']);

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

it('can search trips', function (): void {
    $trips = Trip::factory(5)->create(['user_add' => 'jdupont']);

    $search = $trips->first()->departure_location;

    livewire(ListTrips::class)
        ->loadTable()
        ->searchTable($search)
        ->assertCanSeeTableRecords($trips->where('departure_location', $search))
        ->assertCanNotSeeTableRecords($trips->where('departure_location', '!=', $search));
});

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

it('does not display the departure time inputs for an internal movement', function (array $data): void {
    livewire(CreateTrip::class)
        ->fillForm($data)
        ->assertFormFieldExists('departure_date', checkFieldUsing: fn (DateTimePicker $field): bool => ! $field->hasTime());
})->with([
    'nothing filled' => [['departure_location' => null, 'arrival_location' => null, 'arrival_date' => null]],
    'only arrival_date' => [['departure_location' => null, 'arrival_location' => null, 'arrival_date' => now()]],
    'missing arrival_date' => [['departure_location' => 'Marche', 'arrival_location' => 'Namur', 'arrival_date' => null]],
]);

it('displays the departure time inputs for an external movement', function (): void {
    livewire(CreateTrip::class)
        ->fillForm([
            'departure_location' => 'Marche',
            'arrival_location' => 'Namur',
            'arrival_date' => now(),
        ])
        ->assertFormFieldExists('departure_date', checkFieldUsing: fn (DateTimePicker $field): bool => $field->hasTime());
});

it('requires the three external movement fields when any one is filled', function (array $data, array $errors): void {
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
    'only `departure_location`' => [
        ['departure_location' => 'Marche'],
        ['arrival_location' => 'required_with', 'arrival_date' => 'required_with'],
    ],
    'only `arrival_location`' => [
        ['arrival_location' => 'Namur'],
        ['departure_location' => 'required_with', 'arrival_date' => 'required_with'],
    ],
    'only `arrival_date`' => [
        ['arrival_date' => now()],
        ['departure_location' => 'required_with', 'arrival_location' => 'required_with'],
    ],
]);

it('accepts the trip when all three external movement fields are filled', function (): void {
    $trip = Trip::factory()->make(['user_add' => 'jdupont']);

    livewire(CreateTrip::class)
        ->fillForm([
            'distance' => $trip->distance,
            'departure_date' => $trip->departure_date,
            'content' => $trip->content,
            'departure_location' => 'Marche',
            'arrival_location' => 'Namur',
            'arrival_date' => now(),
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();
});

it('defaults an empty departure date to the arrival day at 08h00', function (): void {
    livewire(CreateTrip::class)
        ->fillForm([
            'departure_date' => null,
            'departure_location' => 'Marche',
            'arrival_location' => 'Namur',
            'arrival_date' => '2026-09-08 17:30:00',
        ])
        // seconds(false) normalises the picker state to the minute.
        ->assertFormSet(['departure_date' => '2026-09-08 08:00']);
});

it('keeps a departure date already encoded when the external fields are filled', function (): void {
    livewire(CreateTrip::class)
        ->fillForm([
            'departure_date' => '2026-09-07 06:15:00',
            'departure_location' => 'Marche',
            'arrival_location' => 'Namur',
            'arrival_date' => '2026-09-08 17:30:00',
        ])
        ->assertFormSet(['departure_date' => '2026-09-07 06:15']);
});

it('leaves the departure date empty while the movement is not external', function (): void {
    livewire(CreateTrip::class)
        ->fillForm([
            'departure_date' => null,
            'departure_location' => 'Marche',
            'arrival_location' => null,
            'arrival_date' => '2026-09-08 17:30:00',
        ])
        ->assertFormSet(['departure_date' => null]);
});

it('stores the departure and arrival of an external movement on the Belgian clock', function (): void {
    // The dates are a wall clock, not an instant: Filament's display timezone
    // must not turn a departure typed at 00h00 on 08-09 into 07-09 22:00.
    $trip = Trip::factory()->make(['user_add' => 'jdupont']);

    livewire(CreateTrip::class)
        ->fillForm([
            'distance' => $trip->distance,
            'content' => $trip->content,
            'departure_location' => 'Marche',
            'arrival_location' => 'Namur',
            'departure_date' => '2026-09-08 00:00:00',
            'arrival_date' => '2026-09-08 08:50:00',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Trip::class, [
        'content' => $trip->content,
        'departure_date' => '2026-09-08 00:00:00',
        'arrival_date' => '2026-09-08 08:50:00',
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
