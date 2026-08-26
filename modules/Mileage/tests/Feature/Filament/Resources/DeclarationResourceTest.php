<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Enums\TypeMovementEnum;
use AcMarche\Mileage\Filament\Pages\AllDeclarations;
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

it('shows the personal information contact details on the view page', function (): void {
    $declaration = Declaration::factory()->create([
        'user_add' => 'jdupont',
        'street' => 'Vieille rue 1',
        'iban' => 'BE68 5390 0754 7034',
    ]);
    PersonalInformation::where('username', 'jdupont')->update([
        'street' => 'Nouvelle rue 2',
        'iban' => 'BE62 5100 0754 7061',
    ]);

    livewire(ViewDeclaration::class, ['record' => $declaration->id])
        ->assertOk()
        ->assertSee('Nouvelle rue 2')
        ->assertSee('BE62 5100 0754 7061')
        ->assertSee('Compte utilisé lors de la déclaration : BE68 5390 0754 7034')
        ->assertDontSee('Vieille rue 1');
});

it('labels the budget article with its codes on the view page', function (): void {
    BudgetArticle::factory()->create([
        'name' => 'Frais de déplacement',
        'functional_code' => '104/123',
        'economic_code' => '48',
    ]);
    $declaration = Declaration::factory()->create([
        'user_add' => 'jdupont',
        'budget_article' => 'Frais de déplacement',
    ]);

    livewire(ViewDeclaration::class, ['record' => $declaration->id])
        ->assertOk()
        ->assertSee('104/123 - 48 Frais de déplacement');
});

it('labels the budget article with its codes in the edit form select', function (): void {
    BudgetArticle::factory()->create([
        'name' => 'Frais de déplacement',
        'functional_code' => '104/123',
        'economic_code' => '48',
    ]);
    $declaration = Declaration::factory()->create([
        'user_add' => 'jdupont',
        'budget_article' => 'Frais de déplacement',
    ]);

    livewire(EditDeclaration::class, ['record' => $declaration->id])
        ->assertOk()
        ->assertSchemaComponentStateSet('budget_article', 'Frais de déplacement');

    expect(BudgetArticle::displayNameOptions())
        ->toBe(['Frais de déplacement' => '104/123 - 48 Frais de déplacement']);
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

it('only lists the current user declarations on the index page', function (): void {
    $mine = Declaration::factory(2)->create(['user_add' => 'jdupont']);
    $others = Declaration::factory(3)->create();
    Declaration::whereKey($others->modelKeys())->update(['user_add' => 'someoneelse']);

    livewire(ListDeclarations::class)
        ->loadTable()
        ->assertCanSeeTableRecords($mine)
        ->assertCanNotSeeTableRecords($others);
});

it('lists every user declaration on the all declarations page', function (): void {
    $mine = Declaration::factory(2)->create(['user_add' => 'jdupont']);
    $others = Declaration::factory(3)->create();
    Declaration::whereKey($others->modelKeys())->update(['user_add' => 'someoneelse']);

    livewire(AllDeclarations::class)
        ->loadTable()
        ->assertCanSeeTableRecords($mine->merge($others));
});

it('can filter the all declarations page by type of movement and creation date', function (): void {
    $internal = Declaration::factory()->create([
        'type_movement' => TypeMovementEnum::INTERNAL->value,
        'created_at' => '2026-06-15',
    ]);
    $external = Declaration::factory()->create([
        'type_movement' => TypeMovementEnum::EXTERNAL->value,
        'created_at' => '2026-06-15',
    ]);
    $old = Declaration::factory()->create([
        'type_movement' => TypeMovementEnum::INTERNAL->value,
        'created_at' => '2026-01-15',
    ]);

    livewire(AllDeclarations::class)
        ->loadTable()
        ->filterTable('type_movement', TypeMovementEnum::INTERNAL->value)
        ->assertCanSeeTableRecords([$internal, $old])
        ->assertCanNotSeeTableRecords([$external])
        // Only one bound of the range: the other key is absent from the filter state.
        ->filterTable('created_at', ['created_from' => '2026-06-01'])
        ->assertCanSeeTableRecords([$internal])
        ->assertCanNotSeeTableRecords([$old]);
});

it('has table columns', function (string $column): void {
    livewire(ListDeclarations::class)
        ->assertTableColumnExists($column);
})->with(['id', 'last_name', 'first_name', 'type_movement']);

it('can filter declarations by type of movement', function (): void {
    $internal = Declaration::factory()->create(['user_add' => 'jdupont', 'type_movement' => TypeMovementEnum::INTERNAL->value]);
    $external = Declaration::factory()->create(['user_add' => 'jdupont', 'type_movement' => TypeMovementEnum::EXTERNAL->value]);

    livewire(ListDeclarations::class)
        ->loadTable()
        ->filterTable('type_movement', TypeMovementEnum::INTERNAL->value)
        ->assertCanSeeTableRecords([$internal])
        ->assertCanNotSeeTableRecords([$external]);
});

it('can filter declarations by creation date', function (): void {
    $old = Declaration::factory()->create(['user_add' => 'jdupont', 'created_at' => '2026-01-15']);
    $recent = Declaration::factory()->create(['user_add' => 'jdupont', 'created_at' => '2026-06-15']);

    livewire(ListDeclarations::class)
        ->loadTable()
        ->filterTable('created_at', ['created_from' => '2026-06-01'])
        ->assertCanSeeTableRecords([$recent])
        ->assertCanNotSeeTableRecords([$old]);
});

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
