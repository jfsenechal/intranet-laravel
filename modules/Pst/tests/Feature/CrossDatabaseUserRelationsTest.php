<?php

declare(strict_types=1);

namespace AcMarche\Pst\Tests\Feature;

use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\OperationalObjective;
use AcMarche\Pst\Models\Service;
use AcMarche\Pst\Models\StrategicObjective;
use AcMarche\Pst\Policies\ActionPolicy;
use App\Models\User;

function createActionForRelations(): Action
{
    $strategicObjective = StrategicObjective::factory()->create();

    return Action::factory()->create([
        'operational_objective_id' => OperationalObjective::factory()->create([
            'strategic_objective_id' => $strategicObjective->id,
        ])->id,
    ]);
}

/**
 * Pst models sit on the maria-pst connection while users live in the intranet database.
 * Eloquent copies the pst connection onto the related User model, so these relations
 * have to qualify the users table themselves.
 *
 * @see \AcMarche\Pst\Models\QualifiedUsersTableTrait
 */
it('resolves the agents of an action', function (): void {
    $user = User::factory()->create();
    $action = createActionForRelations();

    $action->users()->attach($user->username);

    expect($action->users()->pluck('users.username')->all())->toBe([$user->username])
        ->and($action->fresh()->users->pluck('username')->all())->toBe([$user->username]);
});

it('resolves the mandataries of an action', function (): void {
    $user = User::factory()->create();
    $action = createActionForRelations();

    $action->mandataries()->attach($user->username);

    expect($action->mandataries()->pluck('users.username')->all())->toBe([$user->username]);
});

it('resolves the users of a service', function (): void {
    $user = User::factory()->create();
    $service = Service::factory()->create();

    $service->users()->attach($user->username);

    expect($service->users()->pluck('users.username')->all())->toBe([$user->username]);
});

/**
 * whereHas() builds its existence subquery from a fresh query on the related model
 * rather than from the relation, so it needs the users table qualified there too.
 */
it('resolves an existence subquery on the users of a service', function (): void {
    $user = User::factory()->create();
    $service = Service::factory()->create();
    $action = createActionForRelations();

    $service->users()->attach($user->username);
    $action->leaderServices()->attach($service->id);

    $linked = $action->leaderServices()
        ->whereHas('users', fn ($query) => $query->where('service_user.username', $user->username))
        ->exists();

    expect($linked)->toBeTrue()
        ->and(ActionPolicy::isUserLinkedToAction($user, $action))->toBeTrue();
});

it('qualifies the users table only when the databases differ', function (): void {
    $qualify = fn (Action $action): string => (fn (): string => $this->qualifiedUsersTable())->call($action);

    expect($qualify(new Action()))->toBe((new User())->getTable());
});
