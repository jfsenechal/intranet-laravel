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
                            'description' => $fosGroup->description ?? $fosGroup->name,
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

        $this->info('Migration completed!');
        $this->info("✓ Created: {$created} roles");
        if ($skipped > 0) {
            $this->warn("⊘ Skipped: {$skipped} roles (already exist)");
        }

        if (count($errors) > 0) {
            $this->error('Errors encountered:');
            foreach ($errors as $error) {
                $this->error("  • {$error}");
            }

            return SfCommand::FAILURE;
        }

        return SfCommand::SUCCESS;
    }
}
