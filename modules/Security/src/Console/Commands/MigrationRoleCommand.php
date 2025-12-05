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

        $this->info("Found {$fosGroups->count()} groups to migrate.");

        $progressBar = $this->output->createProgressBar($fosGroups->count());
        $progressBar->start();

        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($fosGroups as $fosGroup) {
            try {
                // Check if role already exists
                $existingRole = DB::connection('mariadb')
                    ->table('roles')
                    ->where('name', $fosGroup->name)
                    ->first();

                if ($existingRole) {
                    dump("existingRole: {$existingRole->name}");
                    $skipped++;
                    $progressBar->advance();

                    continue;
                }

                // Create new role
                DB::connection('mariadb')
                    ->table('roles')
                    ->insert([
                        'name' => $fosGroup->name,
                        'description' => $fosGroup->description ?? $fosGroup->title,
                        'module_id' => $fosGroup->module_id,
                    ]);

                $created++;
            } catch (Exception $e) {
                $errors[] = "Failed to migrate group '{$fosGroup->name}': {$e->getMessage()}";
            }

            $progressBar->advance();
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
