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
})->with(['departure_date', 'departure_location', 'distance', 'type_movement']);

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
