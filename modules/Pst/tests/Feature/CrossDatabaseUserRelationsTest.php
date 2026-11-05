<?php

declare(strict_types=1);

namespace AcMarche\Pst\Tests\Feature;

use AcMarche\Pst\Enums\RolesEnum;
use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\OperationalObjective;
use AcMarche\Pst\Models\Service;
use AcMarche\Pst\Models\StrategicObjective;
use AcMarche\Pst\Policies\ActionPolicy;
use AcMarche\Security\Models\Role;
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

/**
 * The pst database has no roles tables, and a whereHas('roles') on these relations would
 * reach them. Every connection shares one sqlite database in tests, which is why the
 * generated sql is asserted as well as the result.
 */
it('filters the users of a relation by role without querying the roles tables', function (): void {
    $mandatary = User::factory()->create();
    $mandatary->addRole(Role::factory()->create(['name' => RolesEnum::MANDATAIRE->value]));
    $otherUser = User::factory()->create();
    $action = createActionForRelations();

    $action->mandataries()->attach([$mandatary->username, $otherUser->username]);

    $query = $action->mandataries()->withRole(RolesEnum::MANDATAIRE->value);

    expect($query->pluck('users.username')->all())->toBe([$mandatary->username])
        ->and($query->toSql())->not->toContain('role');
});

it('qualifies the users table only when the databases differ', function (): void {
    $qualify = fn (Action $action): string => (fn (): string => $this->qualifiedUsersTable())->call($action);

    expect($qualify(new Action()))->toBe((new User())->getTable());
});
