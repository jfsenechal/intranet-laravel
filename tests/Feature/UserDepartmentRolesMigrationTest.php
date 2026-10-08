<?php

declare(strict_types=1);

use AcMarche\News\Enums\RolesEnum as NewsRolesEnum;
use AcMarche\Pst\Enums\RolesEnum as PstRolesEnum;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

const DEPARTMENT_ROLES_MIGRATION = 'database/migrations/2026_10_08_084845_replace_user_departments_with_department_roles.php';
const DROP_DEPARTMENTS_MIGRATION = 'database/migrations/2026_10_08_084846_drop_departments_from_users_table.php';

function departmentMigration(string $path): Migration
{
    return require base_path($path);
}

/**
 * Put the database back in its pre-migration state: the `departments` column
 * exists and ROLE_PST is still held by the PST users.
 */
beforeEach(function (): void {
    departmentMigration(DROP_DEPARTMENTS_MIGRATION)->down();
    departmentMigration(DEPARTMENT_ROLES_MIGRATION)->down();
});

/**
 * @param  list<string>  $departments
 */
function userWithDepartments(array $departments, string ...$roleNames): User
{
    $user = User::factory()->withRoles(...$roleNames)->create();
    DB::table('users')->where('id', $user->id)->update(['departments' => json_encode($departments)]);

    return $user;
}

/**
 * @return list<string>
 */
function roleNamesOf(User $user): array
{
    return $user->roles()->pluck('name')->all();
}

it('gives every user the news role of their departments', function (): void {
    $ville = userWithDepartments(['VILLE']);
    $cpas = userWithDepartments(['CPAS']);
    $both = userWithDepartments(['VILLE', 'CPAS']);
    $none = userWithDepartments([]);

    departmentMigration(DEPARTMENT_ROLES_MIGRATION)->up();

    expect(roleNamesOf($ville))->toBe([NewsRolesEnum::ROLE_NEWS_VILLE->value])
        ->and(roleNamesOf($cpas))->toBe([NewsRolesEnum::ROLE_NEWS_CPAS->value])
        ->and(roleNamesOf($both))->toEqualCanonicalizing([NewsRolesEnum::ROLE_NEWS_VILLE->value, NewsRolesEnum::ROLE_NEWS_CPAS->value])
        ->and(roleNamesOf($none))->toBe([]);
});

it('replaces ROLE_PST by the PST role of the departments of the PST users only', function (): void {
    $pstUser = userWithDepartments(['CPAS'], 'ROLE_PST');
    $pstAdmin = userWithDepartments(['VILLE'], PstRolesEnum::ADMIN->value);
    $other = userWithDepartments(['VILLE']);

    departmentMigration(DEPARTMENT_ROLES_MIGRATION)->up();

    expect(roleNamesOf($pstUser))->toEqualCanonicalizing([NewsRolesEnum::ROLE_NEWS_CPAS->value, PstRolesEnum::CPAS->value])
        ->and(roleNamesOf($pstAdmin))->toEqualCanonicalizing([NewsRolesEnum::ROLE_NEWS_VILLE->value, PstRolesEnum::VILLE->value, PstRolesEnum::ADMIN->value])
        ->and(roleNamesOf($other))->toBe([NewsRolesEnum::ROLE_NEWS_VILLE->value])
        ->and(Role::query()->where('name', 'ROLE_PST')->exists())->toBeFalse();
});

it('skips null or invalid departments left by the legacy data', function (): void {
    $user = User::factory()->create();
    DB::table('users')->where('id', $user->id)->update(['departments' => 'not json']);

    departmentMigration(DEPARTMENT_ROLES_MIGRATION)->up();

    expect(roleNamesOf($user))->toBe([]);
});

it('drops the departments column and rebuilds it from the roles on rollback', function (): void {
    $pstUser = userWithDepartments(['VILLE', 'CPAS'], 'ROLE_PST');
    $other = userWithDepartments(['CPAS']);

    departmentMigration(DEPARTMENT_ROLES_MIGRATION)->up();
    departmentMigration(DROP_DEPARTMENTS_MIGRATION)->up();

    expect(Schema::hasColumn('users', 'departments'))->toBeFalse();

    departmentMigration(DROP_DEPARTMENTS_MIGRATION)->down();
    departmentMigration(DEPARTMENT_ROLES_MIGRATION)->down();

    $departmentsOf = fn (User $user): array => json_decode((string) DB::table('users')->where('id', $user->id)->value('departments'), true);

    expect($departmentsOf($pstUser))->toEqualCanonicalizing(['VILLE', 'CPAS'])
        ->and($departmentsOf($other))->toBe(['CPAS'])
        ->and(roleNamesOf($pstUser))->toBe(['ROLE_PST'])
        ->and(roleNamesOf($other))->toBe([]);
});
