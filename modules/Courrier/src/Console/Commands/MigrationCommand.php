<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Console\Commands;

use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Command\Command as SfCommand;

final class MigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courrier:migration-incoming-mail-recipient {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate user_id to username in incoming_mail_recipient table';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting migration from user_id to username in incoming_mail_recipient');

        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Running in DRY-RUN mode - no data will be saved');
        }

        try {
            // Check if the table has a user_id column
            if (! Schema::connection('maria-courrier')->hasColumn('incoming_mail_recipient', 'user_id')) {
                $this->error('The incoming_mail_recipient table does not have a user_id column');

                return SfCommand::FAILURE;
            }

            // Check if username column exists, if not add it
            if (! Schema::connection('maria-courrier')->hasColumn('incoming_mail_recipient', 'username')) {
                if (! $dryRun) {
                    Schema::connection('maria-courrier')->table('incoming_mail_recipient', function ($table) {
                        $table->string('username')->nullable()->after('id');
                    });
                    $this->info('✓ Added username column to incoming_mail_recipient table');
                } else {
                    $this->info('[DRY-RUN] Would add username column to incoming_mail_recipient table');
                }
            }

            // Fetch all records with user_id
            $recipients = DB::connection('maria-courrier')
                ->table('incoming_mail_recipient')
                ->whereNotNull('user_id')
                ->get();

            $this->info("Found {$recipients->count()} records with user_id to migrate");

            if ($recipients->isEmpty()) {
                $this->info('No records to migrate');

                return SfCommand::SUCCESS;
            }

            $updated = 0;
            $skipped = 0;
            $errors = 0;

            foreach ($recipients as $recipient) {
                try {
                    // Get the user to retrieve the username
                    $user = User::find($recipient->user_id);

                    if (! $user) {
                        $this->warn("User with ID {$recipient->user_id} not found - skipping record ID {$recipient->id}");
                        $skipped++;

                        continue;
                    }

                    // Check if username is already set
                    if ($recipient->username === $user->username) {
                        $this->line("Username already set for record ID {$recipient->id} - skipping");
                        $skipped++;

                        continue;
                    }

                    if (! $dryRun) {
                        // Update the record with username
                        DB::connection('maria-courrier')
                            ->table('incoming_mail_recipient')
                            ->where('id', $recipient->id)
                            ->update(['username' => $user->username]);

                        $this->info("✓ Updated record ID {$recipient->id} with username: {$user->username}");
                    } else {
                        $this->info("[DRY-RUN] Would update record ID {$recipient->id} with username: {$user->username}");
                    }

                    $updated++;
                } catch (Exception $e) {
                    $this->error("Error migrating record ID {$recipient->id}: {$e->getMessage()}");
                    $errors++;
                }
            }

            // After migration, offer to drop the user_id column
            if (! $dryRun && $updated > 0 && $errors === 0) {
                $this->newLine();
                if ($this->confirm('Migration completed successfully. Do you want to drop the user_id column?', false)) {
                    Schema::connection('maria-courrier')->table('incoming_mail_recipient', function ($table) {
                        $table->dropColumn('user_id');
                    });
                    $this->info('✓ Dropped user_id column from incoming_mail_recipient table');
                }
            }

            $this->newLine();
            $this->info('Migration completed!');
            $this->table(
                ['Status', 'Count'],
                [
                    ['Updated', $updated],
                    ['Skipped', $skipped],
                    ['Errors', $errors],
                    ['Total', $recipients->count()],
                ]
            );

            return $errors > 0 ? SfCommand::FAILURE : SfCommand::SUCCESS;
        } catch (Exception $e) {
            $this->error("Migration failed: {$e->getMessage()}");

            return SfCommand::FAILURE;
        }
    }
}
