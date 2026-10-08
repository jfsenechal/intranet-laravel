<?php

declare(strict_types=1);

namespace AcMarche\Pst\Tests\Feature;

use AcMarche\Pst\Enums\RolesEnum;
use AcMarche\Pst\Providers\PstServiceProvider;
use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use App\Models\User;

function createPstModuleWithRoles(): Module
{
    $module = Module::factory()->create(['id' => PstServiceProvider::$module_id]);

    foreach (RolesEnum::cases() as $role) {
        // The department roles already exist, created by their migration.
        Role::query()->updateOrCreate(['name' => $role->value], ['module_id' => $module->id]);
    }

    return $module;
}

/**
 * A trimmed down mysqldump keeping the shape of data/pst.sql: escaped quotes,
 * json columns and NULLs inside the users tuples.
 */
function writeLegacyDump(string $usersRows, string $roleUserRows = ''): string
{
    $file = tempnam(sys_get_temp_dir(), 'pst').'.sql';

    file_put_contents($file, <<<SQL
        -- Déchargement des données de la table `roles`

        INSERT INTO `roles` (`id`, `name`, `description`, `label`) VALUES
        (1, 'ROLE_ADMIN', 'Gestion des utilisateurs, des OS,OO,ODD et services', 'Administrateur'),
        (4, 'ROLE_MANDATAIRE', 'Accès en lecture seul', 'Mandataire');

        INSERT INTO `role_user` (`id`, `user_id`, `role_id`) VALUES
        {$roleUserRows};

        INSERT INTO `users` (`id`, `last_name`, `first_name`, `username`, `departments`, `mobile`) VALUES
        {$usersRows};

        COMMIT;
        SQL);

    return $file;
}

it('gives every legacy user the PST role of their department and maps ROLE_ADMIN to ROLE_PST_ADMIN', function (): void {
    $module = createPstModuleWithRoles();

    $admin = User::factory()->create(['username' => 'jfsenechal']);
    $mandatary = User::factory()->create(['username' => 'jfpierard']);
    $plain = User::factory()->create(['username' => 'igirard']);

    $file = writeLegacyDump(
        usersRows: "(1, 'Sénéchal', 'Jean-François', 'jfsenechal', '[\\\"VILLE\\\"]', '+32476662615'),\n".
            "(2, 'Girard', 'Isabelle', 'igirard', '[\\\"VILLE\\\"]', NULL),\n".
            "(3, 'Piérard', 'Jean-Francois', 'jfpierard', '[\\\"VILLE\\\"]', NULL)",
        roleUserRows: "(1, 1, 1),\n(267, 3, 4)",
    );

    $this->artisan('pst:migrate-roles', ['--file' => $file])
        ->assertSuccessful();

    expect($admin->fresh()->roles->pluck('name')->all())
        ->toEqualCanonicalizing([RolesEnum::VILLE->value, RolesEnum::ADMIN->value])
        ->and($mandatary->fresh()->roles->pluck('name')->all())
        ->toEqualCanonicalizing([RolesEnum::VILLE->value, RolesEnum::MANDATAIRE->value])
        ->and($plain->fresh()->roles->pluck('name')->all())
        ->toBe([RolesEnum::VILLE->value])
        ->and($plain->fresh()->modules->pluck('id')->all())
        ->toBe([$module->id]);

    unlink($file);
});

it('gives the PST role of each department of a legacy user', function (): void {
    createPstModuleWithRoles();

    $cpas = User::factory()->create(['username' => 'cpasuser']);
    $both = User::factory()->create(['username' => 'bothuser']);

    $file = writeLegacyDump(
        usersRows: "(1, 'Cpas', 'User', 'cpasuser', '[\\\"CPAS\\\"]', NULL),\n".
            "(2, 'Both', 'User', 'bothuser', '[\\\"VILLE\\\",\\\"CPAS\\\"]', NULL)",
        roleUserRows: '(1, 9, 1)',
    );

    $this->artisan('pst:migrate-roles', ['--file' => $file])
        ->assertSuccessful();

    expect($cpas->fresh()->roles->pluck('name')->all())
        ->toBe([RolesEnum::CPAS->value])
        ->and($both->fresh()->roles->pluck('name')->all())
        ->toEqualCanonicalizing([RolesEnum::VILLE->value, RolesEnum::CPAS->value]);

    unlink($file);
});

it('reports legacy users without an intranet account and leaves the others alone', function (): void {
    createPstModuleWithRoles();

    $known = User::factory()->create(['username' => 'igirard']);

    $file = writeLegacyDump(
        usersRows: "(1, 'Sénéchal', 'Jean-François', 'jfsenechal', '[\\\"VILLE\\\"]', NULL),\n".
            "(2, 'Girard', 'Isabelle', 'igirard', '[\\\"VILLE\\\"]', NULL)",
        roleUserRows: '(1, 1, 1)',
    );

    $this->artisan('pst:migrate-roles', ['--file' => $file])
        ->expectsOutputToContain('jfsenechal')
        ->assertSuccessful();

    expect($known->fresh()->roles->pluck('name')->all())->toBe([RolesEnum::VILLE->value]);

    unlink($file);
});

it('does not write anything on a dry run', function (): void {
    createPstModuleWithRoles();

    $user = User::factory()->create(['username' => 'jfsenechal']);

    $file = writeLegacyDump(
        usersRows: "(1, 'Sénéchal', 'Jean-François', 'jfsenechal', '[\\\"VILLE\\\"]', NULL)",
        roleUserRows: '(1, 1, 1)',
    );

    $this->artisan('pst:migrate-roles', ['--file' => $file, '--dry-run' => true])
        ->assertSuccessful();

    expect($user->fresh()->roles)->toBeEmpty()
        ->and($user->fresh()->modules)->toBeEmpty();

    unlink($file);
});

it('is idempotent', function (): void {
    createPstModuleWithRoles();

    $user = User::factory()->create(['username' => 'jfsenechal']);

    $file = writeLegacyDump(
        usersRows: "(1, 'Sénéchal', 'Jean-François', 'jfsenechal', '[\\\"VILLE\\\"]', NULL)",
        roleUserRows: '(1, 1, 1)',
    );

    $this->artisan('pst:migrate-roles', ['--file' => $file])->assertSuccessful();
    $this->artisan('pst:migrate-roles', ['--file' => $file])->assertSuccessful();

    expect($user->fresh()->roles->pluck('name')->all())
        ->toEqualCanonicalizing([RolesEnum::VILLE->value, RolesEnum::ADMIN->value])
        ->and($user->fresh()->modules)->toHaveCount(1);

    unlink($file);
});

it('fails when the dump is missing', function (): void {
    createPstModuleWithRoles();

    $this->artisan('pst:migrate-roles', ['--file' => 'data/does-not-exist.sql'])
        ->assertFailed();
});

it('fails when the PST roles are not synced yet', function (): void {
    Module::factory()->create(['id' => PstServiceProvider::$module_id]);

    $file = writeLegacyDump(
        usersRows: "(1, 'Sénéchal', 'Jean-François', 'jfsenechal', '[\\\"VILLE\\\"]', NULL)",
    );

    $this->artisan('pst:migrate-roles', ['--file' => $file])
        ->assertFailed();

    unlink($file);
});
