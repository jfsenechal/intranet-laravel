<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Factory\DeclarationFactory;
use AcMarche\Mileage\Models\BudgetArticle;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\PersonalInformation;
use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Models\Trip;
use AcMarche\Security\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['username' => 'testuser']);
    $this->budgetArticle = BudgetArticle::factory()->create();
});

describe('handleTrips', function () {
    test('creates declarations from trips grouped by type_movement and rate', function () {
        // Create personal information for the user
        $personalInfo = PersonalInformation::factory()->create([
            'username' => $this->user->username,
            'postal_code' => '6900',
            'street' => 'Rue de test',
            'city' => 'Marche',
            'iban' => 'BE68539007547034',
            'car_license_plate1' => '1-ABC-123',
            'omnium' => true,
            'college_trip_date' => '2030-01-15',
        ]);

        // Create rates with unique dates to avoid conflicts with other tests
        $rate1 = Rate::factory()->create([
            'amount' => 0.40,
            'omnium' => 0.02,
            'start_date' => '2030-01-01',
            'end_date' => '2030-06-30',
        ]);

        $rate2 = Rate::factory()->create([
            'amount' => 0.45,
            'omnium' => 0.03,
            'start_date' => '2030-07-01',
            'end_date' => '2030-12-31',
        ]);

        // Create trips with different type_movement and dates
        // Note: TripObserver sets type_movement based on arrival_date:
        // - arrival_date = null -> 'interne'
        // - arrival_date present -> 'externe'
        $trip1 = Trip::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2030-02-15',
            'arrival_date' => null, // This makes it 'interne'
            'declaration_id' => null,
        ]);

        $trip2 = Trip::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2030-03-20',
            'arrival_date' => null, // This makes it 'interne'
            'declaration_id' => null,
        ]);

        $trip3 = Trip::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2030-08-10',
            'arrival_date' => '2030-08-11', // This makes it 'externe'
            'declaration_id' => null,
        ]);

        $trips = collect([$trip1, $trip2, $trip3]);

        // Call the handler
        $declarations = DeclarationFactory::handleTrips($trips, $this->user, $personalInfo, $this->budgetArticle);

        // Assertions - should create 2 declarations (one per type_movement + rate combination)
        expect($declarations)->toHaveCount(2)
            ->and($declarations->first())->toBeInstanceOf(Declaration::class);

        // Get the type_movements from created declarations
        $typeMovements = $declarations->pluck('type_movement')->sort()->values()->all();
        expect($typeMovements)->toBe(['externe', 'interne']);

        // Find the interne declaration (for the first rate period)
        $interneDeclaration = $declarations->firstWhere('type_movement', 'interne');

        // Verify declaration data
        expect($interneDeclaration->last_name)->toBe($this->user->last_name)
            ->and($interneDeclaration->first_name)->toBe($this->user->first_name)
            ->and($interneDeclaration->postal_code)->toBe('6900')
            ->and($interneDeclaration->street)->toBe('Rue de test')
            ->and($interneDeclaration->city)->toBe('Marche')
            ->and($interneDeclaration->iban)->toBe('BE68539007547034')
            ->and($interneDeclaration->rate)->toBe('0.40')
            ->and($interneDeclaration->budget_article)->toBe($this->budgetArticle->name);

        // Verify trips are attached to declarations
        $trip1->refresh();
        $trip2->refresh();
        $trip3->refresh();

        expect($trip1->declaration_id)->not->toBeNull()
            ->and($trip2->declaration_id)->not->toBeNull()
            ->and($trip3->declaration_id)->not->toBeNull()
            ->and($trip1->declaration_id)->toBe($trip2->declaration_id);
    });

    test('returns empty collection when trips array is empty', function () {
        $personalInfo = PersonalInformation::factory()->create([
            'username' => $this->user->username,
        ]);

        $declarations = DeclarationFactory::handleTrips([], $this->user, $personalInfo, $this->budgetArticle);

        expect($declarations)->toBeEmpty();
    });

    test('skips trips without matching rate', function () {
        $personalInfo = PersonalInformation::factory()->create([
            'username' => $this->user->username,
        ]);

        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        // Trip outside rate period
        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'type_movement' => 'interne',
            'departure_date' => '2025-12-31',
            'declaration_id' => null,
        ]);

        $declarations = DeclarationFactory::handleTrips([$trip], $this->user, $personalInfo, $this->budgetArticle);

        expect($declarations)->toBeEmpty();
    });

    test('includes departments for admin role', function () {
        $adminRole = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
        $this->user->roles()->attach($adminRole);

        $personalInfo = PersonalInformation::factory()->create([
            'username' => $this->user->username,
        ]);

        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'type_movement' => 'interne',
            'departure_date' => '2024-06-15',
            'declaration_id' => null,
        ]);

        $declarations = DeclarationFactory::handleTrips([$trip], $this->user, $personalInfo, $this->budgetArticle);

        $declaration = $declarations->first();
        $departments = json_decode($declaration->departments, true);

        expect($departments)->toHaveCount(3)
            ->and($departments)->toContain(RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value)
            ->and($departments)->toContain(RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value)
            ->and($departments)->toContain(RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value);
    });

    test('includes only ville department for ville role', function () {
        $villeRole = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $this->user->roles()->attach($villeRole);

        $personalInfo = PersonalInformation::factory()->create([
            'username' => $this->user->username,
        ]);

        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'type_movement' => 'interne',
            'departure_date' => '2024-06-15',
            'declaration_id' => null,
        ]);

        $declarations = DeclarationFactory::handleTrips([$trip], $this->user, $personalInfo, $this->budgetArticle);

        $declaration = $declarations->first();
        $departments = json_decode($declaration->departments, true);

        expect($departments)->toHaveCount(1)
            ->and($departments)->toContain(RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value);
    });

    test('includes only cpas department for cpas role', function () {
        $cpasRole = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $this->user->roles()->attach($cpasRole);

        $personalInfo = PersonalInformation::factory()->create([
            'username' => $this->user->username,
        ]);

        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'type_movement' => 'interne',
            'departure_date' => '2024-06-15',
            'declaration_id' => null,
        ]);

        $declarations = DeclarationFactory::handleTrips([$trip], $this->user, $personalInfo, $this->budgetArticle);

        $declaration = $declarations->first();
        $departments = json_decode($declaration->departments, true);

        expect($departments)->toHaveCount(1)
            ->and($departments)->toContain(RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value);
    });

    test('handles multiple departments for user with multiple roles', function () {
        $villeRole = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $cpasRole = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]);
        $this->user->roles()->attach([$villeRole->id, $cpasRole->id]);

        $personalInfo = PersonalInformation::factory()->create([
            'username' => $this->user->username,
        ]);

        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'type_movement' => 'interne',
            'departure_date' => '2024-06-15',
            'declaration_id' => null,
        ]);

        $declarations = DeclarationFactory::handleTrips([$trip], $this->user, $personalInfo, $this->budgetArticle);

        $declaration = $declarations->first();
        $departments = json_decode($declaration->departments, true);

        expect($departments)->toHaveCount(2)
            ->and($departments)->toContain(RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value)
            ->and($departments)->toContain(RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value);
    });

    test('groups trips by both type_movement and rate period', function () {
        $personalInfo = PersonalInformation::factory()->create([
            'username' => $this->user->username,
        ]);

        Rate::factory()->create([
            'amount' => 0.40,
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        Rate::factory()->create([
            'amount' => 0.45,
            'start_date' => '2024-07-01',
            'end_date' => '2024-12-31',
        ]);

        // Same type, different rate periods
        $trip1 = Trip::factory()->create([
            'user_id' => $this->user->id,
            'type_movement' => 'interne',
            'departure_date' => '2024-02-15',
            'declaration_id' => null,
        ]);

        $trip2 = Trip::factory()->create([
            'user_id' => $this->user->id,
            'type_movement' => 'interne',
            'departure_date' => '2024-08-15',
            'declaration_id' => null,
        ]);

        $trips = collect([$trip1, $trip2]);

        $declarations = DeclarationFactory::handleTrips($trips, $this->user, $personalInfo, $this->budgetArticle);

        // Should create 2 declarations (same type but different rate periods)
        expect($declarations)->toHaveCount(2);

        $trip1->refresh();
        $trip2->refresh();

        // Trips should have different declaration IDs
        expect($trip1->declaration_id)->not->toBe($trip2->declaration_id);
    });
});
