<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Filament\Resources\Declarations\Pages\CreateDeclaration;
use AcMarche\Mileage\Filament\Resources\Declarations\Pages\EditDeclaration;
use AcMarche\Mileage\Filament\Resources\Declarations\Pages\ListDeclarations;
use AcMarche\Mileage\Filament\Resources\Declarations\Pages\ViewDeclaration;
use AcMarche\Mileage\Filament\Resources\Trips\Pages\ListTrips;
use AcMarche\Mileage\Models\BudgetArticle;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\PersonalInformation;
use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Models\Trip;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
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
    livewire(ListDeclarations::class)
        ->assertOk();
});

it('cannot render the create page', function (): void {
    BudgetArticle::factory()->create();

    livewire(CreateDeclaration::class)
        ->assertForbidden();
});

it('can render the view page', function (): void {
    $declaration = Declaration::factory()->create(['user_add' => 'jdupont']);

    livewire(ViewDeclaration::class, ['record' => $declaration->id])
        ->assertOk();
});

it('can render the edit page', function (): void {
    $declaration = Declaration::factory()->create(['user_add' => 'jdupont']);

    livewire(EditDeclaration::class, ['record' => $declaration->id])
        ->assertOk()
        ->assertSchemaStateSet([
            'last_name' => $declaration->last_name,
            'first_name' => $declaration->first_name,
            'iban' => $declaration->iban,
        ]);
});

it('can list declarations', function (): void {
    $declarations = Declaration::factory(3)->create(['user_add' => 'jdupont']);

    livewire(ListDeclarations::class)
        ->loadTable()
        ->assertCanSeeTableRecords($declarations);
});

it('has table columns', function (string $column): void {
    livewire(ListDeclarations::class)
        ->assertTableColumnExists($column);
})->with(['last_name', 'first_name', 'type_movement']);

it('can search declarations', function (): void {
    $declarations = Declaration::factory(5)->create(['user_add' => 'jdupont']);

    $search = $declarations->first()->last_name;

    livewire(ListDeclarations::class)
        ->loadTable()
        ->searchTable($search)
        ->assertCanSeeTableRecords($declarations->where('last_name', $search))
        ->assertCanNotSeeTableRecords($declarations->where('last_name', '!=', $search));
});

it('can create a declaration via bulk action on trips', function (): void {
    $budgetArticle = BudgetArticle::factory()->create();
    $rate = Rate::factory()->create([
        'start_date' => now()->subMonth(),
        'end_date' => now()->addMonth(),
    ]);
    $trips = Trip::factory(3)->create([
        'user_add' => 'jdupont',
        'departure_date' => now(),
        'declaration_id' => null,
    ]);

    livewire(ListTrips::class)
        ->loadTable()
        ->selectTableRecords($trips)
        ->callAction(TestAction::make('create-declaration')->table()->bulk(), [
            'budget_article_id' => $budgetArticle->id,
        ])
        ->assertNotified();

    expect(Declaration::count())->toBe(1);
    expect(Trip::whereNotNull('declaration_id')->count())->toBe(3);
});

it('can update a declaration', function (): void {
    $declaration = Declaration::factory()->create(['user_add' => 'jdupont']);
    $budgetArticle = BudgetArticle::factory()->create();
    $newIban = fake()->iban('BE');
    $newPlate = fake()->bothify('?-???-###');

    livewire(EditDeclaration::class, ['record' => $declaration->id])
        ->fillForm([
            'budget_article' => $budgetArticle->name,
            'iban' => $newIban,
            'car_license_plate1' => $newPlate,
        ])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(Declaration::class, [
        'id' => $declaration->id,
        'budget_article' => $budgetArticle->name,
        'iban' => $newIban,
        'car_license_plate1' => $newPlate,
    ]);
});

it('can delete a declaration', function (): void {
    $declaration = Declaration::factory()->create(['user_add' => 'jdupont']);

    livewire(EditDeclaration::class, ['record' => $declaration->id])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertSoftDeleted($declaration);
});

it('validates the form data', function (array $data, array $errors): void {
    $declaration = Declaration::factory()->create(['user_add' => 'jdupont']);
    $newData = Declaration::factory()->make(['user_add' => 'jdupont']);
    BudgetArticle::factory()->create();

    livewire(EditDeclaration::class, ['record' => $declaration->id])
        ->fillForm([
            'budget_article' => BudgetArticle::first()->name,
            'iban' => $newData->iban,
            'car_license_plate1' => $newData->car_license_plate1,
            ...$data,
        ])
        ->call('save')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`iban` is required' => [['iban' => null], ['iban' => 'required']],
    '`car_license_plate1` is required' => [['car_license_plate1' => null], ['car_license_plate1' => 'required']],
]);
