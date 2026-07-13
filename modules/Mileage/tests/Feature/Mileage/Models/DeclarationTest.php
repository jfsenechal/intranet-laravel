<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Models\Declaration;

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
