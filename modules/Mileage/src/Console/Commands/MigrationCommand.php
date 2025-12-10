<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Console\Commands;

use AcMarche\Mileage\Models\PersonalInformation;
use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as SfCommand;

final class MigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mileage:migration {--dry-run : Run without making changes}';

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
        $this->info('Starting migration from intranet.profils to personal_information');

        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Running in DRY-RUN mode - no data will be saved');
        }

        try {
            // Fetch all profils from the old intranet database
            $profils = DB::connection('mariadb')
                ->table('profils')
                ->get();

            $this->info("Found {$profils->count()} profils to migrate");

            $created = 0;
            $skipped = 0;
            $errors = 0;

            foreach ($profils as $profil) {
                try {
                    // Get the user to retrieve the username
                    $user = User::find($profil->user_id);

                    if (! $user) {
                        $this->warn("User with ID {$profil->user_id} not found - skipping profil ID {$profil->id}");
                        $skipped++;

                        continue;
                    }

                    // Check if PersonalInformation already exists for this user
                    $existing = PersonalInformation::where('username', $user->username)->first();

                    if ($existing) {
                        $this->line("PersonalInformation already exists for user {$user->username} - skipping");
                        $skipped++;

                        continue;
                    }

                    if (! $dryRun) {
                        // Create PersonalInformation record
                        PersonalInformation::create([
                            'username' => $user->username,
                            'car_license_plate1' => $profil->car_license_plate1,
                            'car_license_plate2' => $profil->car_license_plate2,
                            'street' => $profil->street,
                            'postal_code' => (string) $profil->postal_code,
                            'city' => $profil->city,
                            'iban' => $profil->iban,
                            'omnium' => (bool) $profil->omnium,
                            'college_trip_date' => $profil->college_trip_date,
                        ]);

                        $this->info("✓ Created PersonalInformation for user {$user->username}");
                    } else {
                        $this->info("[DRY-RUN] Would create PersonalInformation for user {$user->username}");
                    }

                    $created++;
                } catch (Exception $e) {
                    $this->error("Error migrating profil ID {$profil->id}: {$e->getMessage()}");
                    $errors++;
                }
            }

            $this->newLine();
            $this->info('Migration completed!');
            $this->table(
                ['Status', 'Count'],
                [
                    ['Created', $created],
                    ['Skipped', $skipped],
                    ['Errors', $errors],
                    ['Total', $profils->count()],
                ]
            );

            return $errors > 0 ? SfCommand::FAILURE : SfCommand::SUCCESS;
        } catch (Exception $e) {
            $this->error("Migration failed: {$e->getMessage()}");

            return SfCommand::FAILURE;
        }
    }
}
