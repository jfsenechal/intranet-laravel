<?php

declare(strict_types=1);

namespace AcMarche\Pst\Tests\Feature;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Pst\Enums\ActionScopeEnum;
use AcMarche\Pst\Models\OperationalObjective;
use AcMarche\Pst\Models\StrategicObjective;

/**
 * A saving() hook used to null the department of every INTERNAL objective, which the
 * form never asks for (department is required there) and which no query can match:
 * DepartmentScope filters on strict equality, so a null row is invisible everywhere.
 */
it('keeps the department of an internal objective', function (): void {
    $objective = OperationalObjective::factory()->create([
        'strategic_objective_id' => StrategicObjective::factory()->create()->id,
        'department' => DepartmentEnum::VILLE->value,
        'scope' => ActionScopeEnum::INTERNAL,
    ]);

    expect($objective->department)->toBe(DepartmentEnum::VILLE)
        ->and($objective->fresh()->department)->toBe(DepartmentEnum::VILLE)
        ->and($objective->isInternal())->toBeTrue();
});

it('keeps the department of an internal objective on update', function (): void {
    $objective = OperationalObjective::factory()->create([
        'strategic_objective_id' => StrategicObjective::factory()->create()->id,
        'department' => DepartmentEnum::CPAS->value,
        'scope' => ActionScopeEnum::EXTERNAL,
    ]);

    $objective->update(['scope' => ActionScopeEnum::INTERNAL]);

    expect($objective->fresh()->department)->toBe(DepartmentEnum::CPAS);
});

it('finds an internal objective through the department scope', function (): void {
    $objective = OperationalObjective::factory()->create([
        'strategic_objective_id' => StrategicObjective::factory()->create()->id,
        'department' => DepartmentEnum::VILLE->value,
        'scope' => ActionScopeEnum::INTERNAL,
    ]);

    expect(OperationalObjective::query()->forDepartment(DepartmentEnum::VILLE->value)->pluck('id')->all())
        ->toContain($objective->id);
});
