<?php

declare(strict_types=1);

namespace AcMarche\Security\Console\Commands;

use DB;
use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SfCommand;

final class MigrationRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intranet:migration-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Du vieux intranet vers le nouveau';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting role migration from fos_group to roles...');

        // Retrieve all groups from the old intranet
        $fosGroups = DB::connection('mariadb')
            ->table('fos_group')
            ->get();

        if ($fosGroups->isEmpty()) {
            $this->warn('No groups found in fos_group table.');

            return SfCommand::SUCCESS;
        }

        $this->info("Found {$fosGroups->count()} groups to process.");

        $created = 0;
        $skipped = 0;
        $errors = [];
        $totalRoles = 0;

        // Count total roles to migrate
        foreach ($fosGroups as $fosGroup) {
            // skip intranet admin
            if ($fosGroup->id === 61) {
                continue;
            }
            if (! empty($fosGroup->roles)) {
                $rolesArray = json_decode($fosGroup->roles, true);
                if (is_array($rolesArray)) {
                    $totalRoles += count($rolesArray);
                }
            }
        }

        if ($totalRoles === 0) {
            $this->warn('No roles found in fos_group.roles field.');

            return SfCommand::SUCCESS;
        }

        $this->info("Found {$totalRoles} roles to migrate.");

        $progressBar = $this->output->createProgressBar($totalRoles);
        $progressBar->start();

        foreach ($fosGroups as $fosGroup) {
            if (empty($fosGroup->roles)) {
                continue;
            }
            // skip intranet admin
            if ($fosGroup->id === 61) {
                continue;
            }

            $rolesArray = json_decode($fosGroup->roles, true);

            if (! is_array($rolesArray)) {
                $errors[] = "Invalid JSON in group ID {$fosGroup->id}";

                continue;
            }

            foreach ($rolesArray as $roleName) {
                try {
                    // Check if role already exists
                    $existingRole = DB::connection('mariadb')
                        ->table('roles')
                        ->where('name', $roleName)
                        ->first();

                    if ($existingRole) {
                        $skipped++;
                        $progressBar->advance();

                        continue;
                    }

                    // Create new role
                    DB::connection('mariadb')
                        ->table('roles')
                        ->insert([
                            'name' => $roleName,
                            'description' => $fosGroup->description ?? null,
                            'module_id' => $fosGroup->module_id,
                        ]);

                    $created++;
                } catch (Exception $e) {
                    $errors[] = "Failed to migrate role '{$roleName}' from group '{$fosGroup->name}': {$e->getMessage()}";
                }

                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info('✓ Role migration completed!');
        $this->info("  Created: {$created} roles");
        if ($skipped > 0) {
            $this->info("  Skipped: {$skipped} roles (already exist)");
        }

        // Now migrate user-role relationships
        $this->newLine();
        $this->info('Starting user-role relationship migration from fos_user_group...');

        $userGroups = DB::connection('mariadb')
            ->table('fos_user_group')
            ->get();

        if ($userGroups->isEmpty()) {
            $this->warn('No user-group relationships found.');

            if (count($errors) > 0) {
                $this->error('Errors encountered during role migration:');
                foreach ($errors as $error) {
                    $this->error("  • {$error}");
                }

                return SfCommand::FAILURE;
            }

            return SfCommand::SUCCESS;
        }

        $this->info("Found {$userGroups->count()} user-group relationships to process.");

        $relationshipsCreated = 0;
        $relationshipsSkipped = 0;

        $progressBar = $this->output->createProgressBar($userGroups->count());
        $progressBar->start();

        foreach ($userGroups as $userGroup) {
            // Get the group and its roles
            $fosGroup = DB::connection('mariadb')
                ->table('fos_group')
                ->where('id', $userGroup->group_id)
                ->first();

            if (! $fosGroup || empty($fosGroup->roles)) {
                $progressBar->advance();

                continue;
            }

            $rolesArray = json_decode($fosGroup->roles, true);

            if (! is_array($rolesArray)) {
                $errors[] = "Invalid JSON in group ID {$fosGroup->id} for user {$userGroup->user_id}";
                $progressBar->advance();

                continue;
            }

            // For each role in the group, create user-role relationship
            foreach ($rolesArray as $roleName) {
                try {
                    // Get the role ID
                    $role = DB::connection('mariadb')
                        ->table('roles')
                        ->where('name', $roleName)
                        ->first();

                    if (! $role) {
                        $errors[] = "Role '{$roleName}' not found for user {$userGroup->user_id}";

                        continue;
                    }

                    // Check if relationship already exists
                    $existingRelationship = DB::connection('mariadb')
                        ->table('role_user')
                        ->where('user_id', $userGroup->user_id)
                        ->where('role_id', $role->id)
                        ->first();

                    if ($existingRelationship) {
                        $relationshipsSkipped++;

                        continue;
                    }

                    // Create user-role relationship
                    DB::connection('mariadb')
                        ->table('role_user')
                        ->insert([
                            'user_id' => $userGroup->user_id,
                            'role_id' => $role->id,
                        ]);

                    $relationshipsCreated++;
                } catch (Exception $e) {
                    $errors[] = "Failed to create relationship for user {$userGroup->user_id} and role '{$roleName}': {$e->getMessage()}";
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info('✓ User-role migration completed!');
        $this->info("  Created: {$relationshipsCreated} user-role relationships");
        if ($relationshipsSkipped > 0) {
            $this->info("  Skipped: {$relationshipsSkipped} relationships (already exist)");
        }

        // Now migrate user-module relationships
        $this->newLine();
        $this->info('Starting user-module relationship migration...');

        // Get all distinct user-module combinations from role_user and roles tables
        $userModules = DB::connection('mariadb')
            ->table('role_user')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->select('role_user.user_id', 'roles.module_id')
            ->distinct()
            ->get();

        if ($userModules->isEmpty()) {
            $this->warn('No user-module relationships found.');

            if (count($errors) > 0) {
                $this->newLine();
                $this->error('Errors encountered:');
                foreach ($errors as $error) {
                    $this->error("  • {$error}");
                }

                return SfCommand::FAILURE;
            }

            $this->newLine();
            $this->info('✓ All migrations completed successfully!');

            return SfCommand::SUCCESS;
        }

        $this->info("Found {$userModules->count()} user-module relationships to create.");

        $moduleRelationshipsCreated = 0;
        $moduleRelationshipsSkipped = 0;

        $progressBar = $this->output->createProgressBar($userModules->count());
        $progressBar->start();

        foreach ($userModules as $userModule) {
            try {
                // Check if relationship already exists
                $existingModuleRelation = DB::connection('mariadb')
                    ->table('module_user')
                    ->where('user_id', $userModule->user_id)
                    ->where('module_id', $userModule->module_id)
                    ->first();

                if ($existingModuleRelation) {
                    $moduleRelationshipsSkipped++;
                    $progressBar->advance();

                    continue;
                }

                // Create user-module relationship
                DB::connection('mariadb')
                    ->table('module_user')
                    ->insert([
                        'user_id' => $userModule->user_id,
                        'module_id' => $userModule->module_id,
                    ]);

                $moduleRelationshipsCreated++;
            } catch (Exception $e) {
                $errors[] = "Failed to create module relationship for user {$userModule->user_id} and module {$userModule->module_id}: {$e->getMessage()}";
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info('✓ User-module migration completed!');
        $this->info("  Created: {$moduleRelationshipsCreated} user-module relationships");
        if ($moduleRelationshipsSkipped > 0) {
            $this->info("  Skipped: {$moduleRelationshipsSkipped} relationships (already exist)");
        }

        if (count($errors) > 0) {
            $this->newLine();
            $this->error('Errors encountered:');
            foreach ($errors as $error) {
                $this->error("  • {$error}");
            }

            return SfCommand::FAILURE;
        }

        $this->newLine();
        $this->info('✓ All migrations completed successfully!');

        return SfCommand::SUCCESS;
    }
}
