<?php

declare(strict_types=1);

namespace AcMarche\Pst\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Override;
use Symfony\Component\Console\Command\Command as SfCommand;

final class FixCommand extends Command
{
    #[Override]
    protected $signature = 'pst:migration';

    #[Override]
    protected $description = 'Migrate action_user, action_mandatory and service_user tables from user_id to username';

    public function handle(): int
    {
        $tables = ['action_user', 'action_mandatory', 'service_user'];

        foreach ($tables as $table) {
            $this->migrateTable($table);
        }

        $this->info('Migration completed successfully.');

        return SfCommand::SUCCESS;
    }

    private function migrateTable(string $table): void
    {
        if (! Schema::connection('maria-pst')->hasColumn($table, 'user_id')) {
            $this->warn('Table '.$table.' does not have user_id column, skipping.');

            return;
        }

        if (Schema::connection('maria-pst')->hasColumn($table, 'username')) {
            $this->warn('Table '.$table.' already has username column, skipping.');

            return;
        }

        $this->info('Migrating '.$table.'...');

        // Add username column
        Schema::connection('maria-pst')->table($table, function ($blueprint) use ($table): void {
            // service_user doesn't have action_id, add after service_id instead
            if ($table === 'service_user') {
                $blueprint->string('username')->nullable()->after('service_id');
            } else {
                $blueprint->string('username')->nullable()->after('action_id');
            }
        });

        // Populate username from users table in intranet database
        DB::connection('maria-pst')->table($table)
            ->join(DB::raw('`intranet`.`users`'), $table.'.user_id', '=', 'users.id')
            ->update([$table.'.username' => DB::raw('users.username')]);

        // Remove orphaned rows where user no longer exists
        $orphaned = DB::connection('maria-pst')->table($table)->whereNull('username')->count();
        if ($orphaned > 0) {
            $this->warn('Removing '.$orphaned.' orphaned rows from '.$table.' (user no longer exists).');
            DB::connection('maria-pst')->table($table)->whereNull('username')->delete();
        }

        // Drop old FK, unique index, and user_id column
        try {
            Schema::connection('maria-pst')->table($table, function ($blueprint) use ($table): void {
                $blueprint->dropForeign($table.'_user_id_foreign');
            });
        } catch (Exception) {
            // Foreign key may not exist
        }

        try {
            Schema::connection('maria-pst')->table($table, function ($blueprint) use ($table): void {
                if ($table === 'service_user') {
                    $blueprint->dropUnique($table.'_user_id_service_id_unique');
                } else {
                    $blueprint->dropUnique($table.'_action_id_user_id_unique');
                }
            });
        } catch (Exception) {
            // Unique constraint may not exist
        }

        try {
            Schema::connection('maria-pst')->table($table, function ($blueprint): void {
                $blueprint->dropColumn('user_id');
            });
        } catch (Exception) {
            $this->warn('user_id column may not exist in table '.$table.'.');
        }

        // Make username non-nullable and add new unique constraint
        Schema::connection('maria-pst')->table($table, function ($blueprint) use ($table): void {
            $blueprint->string('username')->nullable(false)->change();
            if ($table === 'service_user') {
                $blueprint->unique(['service_id', 'username']);
            } else {
                $blueprint->unique(['action_id', 'username']);
            }
        });

        $count = DB::connection('maria-pst')->table($table)->count();
        $this->info('Migrated '.$table.': '.$count.' rows.');
    }
}
