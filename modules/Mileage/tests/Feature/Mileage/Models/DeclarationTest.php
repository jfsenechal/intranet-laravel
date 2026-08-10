<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\PersonalInformation;
use App\Models\User;

/**
 * The declarations are linked to their personal information through `user_add`,
 * which {@see AcMarche\Security\Models\HasUserAdd} fills with the logged in username.
 */
beforeEach(function (): void {
    $this->actingAs(User::factory()->create(['username' => 'jdupont']));
});

test('isCpas is true when departments references the CPAS role', function (): void {
    $declaration = Declaration::factory()->make([
        'departments' => json_encode([RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]),
    ]);

    expect($declaration->isCpas())->toBeTrue();
});

test('isCpas is false for a Ville declaration', function (): void {
    $declaration = Declaration::factory()->make([
        'departments' => json_encode([RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]),
    ]);

    expect($declaration->isCpas())->toBeFalse();
});

test('isCpas is false when the declaration references both entities (Ville wins)', function (): void {
    $declaration = Declaration::factory()->make([
        'departments' => json_encode([
            RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value,
            RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value,
        ]),
    ]);

    expect($declaration->isCpas())->toBeFalse();
});

test('isCpas is false when departments is null', function (): void {
    $declaration = Declaration::factory()->make(['departments' => null]);

    expect($declaration->isCpas())->toBeFalse();
});

test('the displayed contact details come from the personal information', function (): void {
    $declaration = Declaration::factory()->create([
        'user_add' => 'jdupont',
        'street' => 'Vieille rue 1',
        'postal_code' => '6900',
        'city' => 'Marche',
        'iban' => 'BE68 5390 0754 7034',
    ]);
    PersonalInformation::factory()->create([
        'username' => 'jdupont',
        'street' => 'Nouvelle rue 2',
        'postal_code' => '6987',
        'city' => 'Rendeux',
        'iban' => 'BE62 5100 0754 7061',
    ]);

    expect($declaration->display_street)->toBe('Nouvelle rue 2')
        ->and($declaration->display_postal_code)->toBe('6987')
        ->and($declaration->display_city)->toBe('Rendeux')
        ->and($declaration->display_iban)->toBe('BE62 5100 0754 7061');
});

test('the declaration contact details are used when the personal information is missing', function (): void {
    $declaration = Declaration::factory()->create([
        'user_add' => 'jdupont',
        'street' => 'Vieille rue 1',
        'postal_code' => '6900',
        'city' => 'Marche',
        'iban' => 'BE68 5390 0754 7034',
    ]);

    expect($declaration->display_street)->toBe('Vieille rue 1')
        ->and($declaration->display_postal_code)->toBe('6900')
        ->and($declaration->display_city)->toBe('Marche')
        ->and($declaration->display_iban)->toBe('BE68 5390 0754 7034')
        ->and($declaration->hasOutdatedIban())->toBeFalse();
});

test('the declaration contact details are used for the fields left empty in the personal information', function (): void {
    $declaration = Declaration::factory()->create([
        'user_add' => 'jdupont',
        'street' => 'Vieille rue 1',
        'postal_code' => '6900',
        'city' => 'Marche',
        'iban' => 'BE68 5390 0754 7034',
    ]);
    PersonalInformation::factory()->create([
        'username' => 'jdupont',
        'street' => 'Nouvelle rue 2',
        'postal_code' => null,
        'city' => null,
        'iban' => null,
    ]);

    expect($declaration->display_street)->toBe('Nouvelle rue 2')
        ->and($declaration->display_postal_code)->toBe('6900')
        ->and($declaration->display_city)->toBe('Marche')
        ->and($declaration->display_iban)->toBe('BE68 5390 0754 7034')
        ->and($declaration->hasOutdatedIban())->toBeFalse();
});

test('hasOutdatedIban is true when the personal information holds another account', function (): void {
    $declaration = Declaration::factory()->create([
        'user_add' => 'jdupont',
        'iban' => 'BE68 5390 0754 7034',
    ]);
    PersonalInformation::factory()->create([
        'username' => 'jdupont',
        'iban' => 'BE62 5100 0754 7061',
    ]);

    expect($declaration->hasOutdatedIban())->toBeTrue();
});

test('hasOutdatedIban ignores formatting differences', function (): void {
    $declaration = Declaration::factory()->create([
        'user_add' => 'jdupont',
        'iban' => 'BE68 5390 0754 7034',
    ]);
    PersonalInformation::factory()->create([
        'username' => 'jdupont',
        'iban' => 'be6853900754.7034',
    ]);

    expect($declaration->hasOutdatedIban())->toBeFalse();
});
