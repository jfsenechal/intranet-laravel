<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Console\Commands;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Override;

final class MergeCommand extends Command
{
    #[Override]
    protected $signature = 'courrier:merge
        {--dry-run : Run without making changes}
        {--target=indicateur_ville : Target database name}';

    #[Override]
    protected $description = 'Merge indicateur_cpas and indicateur_bgm legacy databases into the target database with department field';

    private bool $dryRun = false;

    private array $idMappings = [];

    private array $sourceConfigs = [];

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');
        $targetDatabase = $this->option('target');

        if ($this->dryRun) {
            $this->warn('Running in DRY-RUN mode - no data will be saved');
        }

        $this->sourceConfigs = [
            DepartmentCourrierEnum::CPAS->value => 'indicateur_cpas',
            DepartmentCourrierEnum::BGM->value => 'indicateur_bgm',
        ];

        $this->info("Target database: {$targetDatabase}");
        $this->info('Source databases: '.implode(', ', $this->sourceConfigs));
        $this->newLine();

       /* if (! $this->confirm('This will merge data from CPAS and BGM databases into the target. Continue?')) {
            $this->info('Operation cancelled.');

            return self::SUCCESS;
        }*/

        foreach ($this->sourceConfigs as $department => $sourceDatabase) {
            $this->info("Processing {$department} from {$sourceDatabase}...");
            $this->newLine();

            try {
                if ($this->dryRun) {
                    $this->mergeDatabase($sourceDatabase, $targetDatabase, $department);
                } else {
                    // Wrap each department in a transaction so a failure leaves no
                    // partial data behind and the command can be safely re-run.
                    DB::transaction(function () use ($sourceDatabase, $targetDatabase, $department): void {
                        $this->mergeDatabase($sourceDatabase, $targetDatabase, $department);
                    });
                }
            } catch (Exception $e) {
                $this->error("Error processing {$department}: ".$e->getMessage());

                return self::FAILURE;
            }
        }

        $this->updateExistingVilleRecords($targetDatabase);

        $this->newLine();
        $this->info('Merge completed successfully!');
        $this->displaySummary();

        return self::SUCCESS;
    }

    private function mergeDatabase(string $sourceDatabase, string $targetDatabase, string $department): void
    {
        $this->idMappings[$department] = [
            'categories' => [],
            'services' => [],
            'senders' => [],
            'recipients' => [],
            'incoming_mails' => [],
        ];

        $this->mergeCategories($sourceDatabase, $targetDatabase, $department);
        $this->mergeServices($sourceDatabase, $targetDatabase, $department);
        $this->mergeSenders($sourceDatabase, $targetDatabase, $department);
        $this->mergeRecipients($sourceDatabase, $targetDatabase, $department);
        $this->mergeIncomingMails($sourceDatabase, $targetDatabase, $department);
        $this->mergeAttachments($sourceDatabase, $targetDatabase, $department);
        $this->mergePivotTables($sourceDatabase, $targetDatabase, $department);
    }

    private function mergeCategories(string $source, string $target, string $department): void
    {
        if (! $this->tableExists($source, 'categorie')) {
            $this->line('  - Skipping categories (no `categorie` table in source)');

            return;
        }

        $this->info('  - Merging categories...');

        $categories = DB::select("SELECT * FROM {$source}.categorie");

        foreach ($categories as $category) {
            $oldId = $category->id;

            $existing = DB::selectOne(
                "SELECT id FROM {$target}.courrier_categories WHERE name = ?",
                [$category->nom]
            );

            if ($existing) {
                $this->idMappings[$department]['categories'][$oldId] = $existing->id;
                $this->line("    Skipping duplicate category: {$category->nom}");

                continue;
            }

            if (! $this->dryRun) {
                DB::insert(
                    "INSERT INTO {$target}.courrier_categories (old_id, name, color, created_at, updated_at) VALUES (?, ?, ?, ?, ?)",
                    [$oldId, $category->nom, $this->normalizeColor($category->couleur), now(), now()]
                );
                $newId = DB::getPdo()->lastInsertId();
                $this->idMappings[$department]['categories'][$oldId] = $newId;
            }
        }

        $this->info('    Categories: '.count($categories).' processed');
    }

    private function mergeServices(string $source, string $target, string $department): void
    {
        if (! $this->tableExists($source, 'service')) {
            $this->line('  - Skipping services (no `service` table in source)');

            return;
        }

        $this->info('  - Merging services...');

        $services = DB::select("SELECT * FROM {$source}.service");

        foreach ($services as $service) {
            $oldId = $service->id;

            $existing = DB::selectOne(
                "SELECT id FROM {$target}.courrier_services WHERE old_id = ? AND department = ?",
                [$oldId, $department]
            );
            if ($existing) {
                $this->idMappings[$department]['services'][$oldId] = $existing->id;

                continue;
            }

            if (! $this->dryRun) {
                DB::insert(
                    "INSERT INTO {$target}.courrier_services (old_id, slugname, name, initials, actif, department) VALUES (?, ?, ?, ?, ?, ?)",
                    [
                        $oldId,
                        $this->makeSlug($service->slugname, $department, $oldId),
                        $service->nom,
                        $service->initials,
                        1,
                        $department,
                    ]
                );
                $newId = DB::getPdo()->lastInsertId();
                $this->idMappings[$department]['services'][$oldId] = $newId;
            }
        }

        $this->info('    Services: '.count($services).' processed');
    }

    private function mergeSenders(string $source, string $target, string $department): void
    {
        if (! $this->tableExists($source, 'expediteur')) {
            $this->line('  - Skipping senders (no `expediteur` table in source)');

            return;
        }

        $this->info('  - Merging senders...');

        $senders = DB::select("SELECT * FROM {$source}.expediteur");

        foreach ($senders as $sender) {
            $oldId = $sender->id;

            $existing = DB::selectOne(
                "SELECT id FROM {$target}.courrier_senders WHERE old_id = ? AND department = ?",
                [$oldId, $department]
            );
            if ($existing) {
                $this->idMappings[$department]['senders'][$oldId] = $existing->id;

                continue;
            }

            if (! $this->dryRun) {
                DB::insert(
                    "INSERT INTO {$target}.courrier_senders (old_id, slug, name, department) VALUES (?, ?, ?, ?)",
                    [
                        $oldId,
                        $this->makeSlug($sender->slugname, $department, $oldId),
                        $sender->nom,
                        $department,
                    ]
                );
                $newId = DB::getPdo()->lastInsertId();
                $this->idMappings[$department]['senders'][$oldId] = $newId;
            }
        }

        $this->info('    Senders: '.count($senders).' processed');
    }

    private function mergeRecipients(string $source, string $target, string $department): void
    {
        if (! $this->tableExists($source, 'destinataire')) {
            $this->line('  - Skipping recipients (no `destinataire` table in source)');

            return;
        }

        $this->info('  - Merging recipients...');

        // Order supervisors (tuteur_id IS NULL) first so their new IDs are mapped
        // before recipients that reference them.
        $recipients = DB::select("SELECT * FROM {$source}.destinataire ORDER BY tuteur_id IS NOT NULL ASC, tuteur_id ASC");

        foreach ($recipients as $recipient) {
            $oldId = $recipient->id;

            // Recipients are produced by CPAS only, so old_id alone is unique
            // (the `recipients` table has no department column).
            $existing = DB::selectOne(
                "SELECT id FROM {$target}.recipients WHERE old_id = ?",
                [$oldId]
            );
            if ($existing) {
                $this->idMappings[$department]['recipients'][$oldId] = $existing->id;

                continue;
            }

            $newSupervisorId = null;
            if ($recipient->tuteur_id !== null) {
                $newSupervisorId = $this->idMappings[$department]['recipients'][$recipient->tuteur_id] ?? null;
            }

            if (! $this->dryRun) {
                DB::insert(
                    "INSERT INTO {$target}.recipients (old_id, supervisor_id, slug, last_name, first_name, username, email, actif, receives_attachments) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $oldId,
                        $newSupervisorId,
                        $this->makeSlug($recipient->slugname, $department, $oldId),
                        $recipient->nom,
                        $recipient->prenom ?? '',
                        $recipient->username ?? '',
                        $recipient->email ?? '',
                        $recipient->actif ?? 1,
                        0,
                    ]
                );
                $newId = DB::getPdo()->lastInsertId();
                $this->idMappings[$department]['recipients'][$oldId] = $newId;
            }
        }

        $this->info('    Recipients: '.count($recipients).' processed');
    }

    private function mergeIncomingMails(string $source, string $target, string $department): void
    {
        if (! $this->tableExists($source, 'courrier')) {
            $this->line('  - Skipping incoming mails (no `courrier` table in source)');

            return;
        }

        $this->info('  - Merging incoming mails...');

        $mails = DB::select("SELECT * FROM {$source}.courrier");

        foreach ($mails as $mail) {
            $oldId = $mail->id;

            $existing = DB::selectOne(
                "SELECT id FROM {$target}.incoming_mails WHERE old_id = ? AND department = ?",
                [$oldId, $department]
            );
            if ($existing) {
                $this->idMappings[$department]['incoming_mails'][$oldId] = $existing->id;

                continue;
            }

            $newCategoryId = null;
            if (property_exists($mail, 'categorie_id') && $mail->categorie_id !== null) {
                $newCategoryId = $this->idMappings[$department]['categories'][$mail->categorie_id] ?? null;
            }

            // The new `follow_up_note` field gathers the legacy free-text notes:
            // CPAS stores them in `suivi`, BGM in `classement`.
            $followUpNote = property_exists($mail, 'suivi') ? $mail->suivi : null;
            if (property_exists($mail, 'classement') && filled($mail->classement)) {
                $followUpNote = mb_trim(($followUpNote !== null && $followUpNote !== '' ? $followUpNote."\n" : '').'Classement: '.$mail->classement);
            }

            if (! $this->dryRun) {
                DB::insert(
                    "INSERT INTO {$target}.incoming_mails
                    (old_id, category_id, reference_number, sender, description, follow_up_note, mail_date, is_notified, is_registered, has_acknowledgment, user_add, created_at, updated_at, department)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $oldId,
                        $newCategoryId,
                        (string) $mail->numero,
                        $mail->expediteur,
                        $mail->description,
                        $followUpNote,
                        $mail->date_courrier,
                        $mail->notifie,
                        $mail->recommande,
                        $mail->accuse,
                        $mail->user_add ?? '',
                        $mail->created ?? now(),
                        $mail->updated ?? now(),
                        $department,
                    ]
                );
                $newId = DB::getPdo()->lastInsertId();
                $this->idMappings[$department]['incoming_mails'][$oldId] = $newId;
            }
        }

        $this->info('    Incoming mails: '.count($mails).' processed');
    }

    private function mergeAttachments(string $source, string $target, string $department): void
    {
        if (! $this->tableExists($source, 'attachement')) {
            $this->line('  - Skipping attachments (no `attachement` table in source)');

            return;
        }

        $this->info('  - Merging attachments...');

        $attachments = DB::select("SELECT * FROM {$source}.attachement");

        foreach ($attachments as $attachment) {
            $newMailId = $this->idMappings[$department]['incoming_mails'][$attachment->courrier_id] ?? null;

            if ($newMailId === null) {
                $this->warn("    Skipping orphan attachment: {$attachment->file_name}");

                continue;
            }

            // No department column on attachments; the resolved (department-scoped)
            // incoming_mail_id together with old_id uniquely identifies the row.
            $existing = DB::selectOne(
                "SELECT id FROM {$target}.attachments WHERE old_id = ? AND incoming_mail_id = ?",
                [$attachment->id, $newMailId]
            );
            if ($existing) {
                continue;
            }

            if (! $this->dryRun) {
                DB::insert(
                    "INSERT INTO {$target}.attachments (old_id, incoming_mail_id, file_name, mime, updated_at) VALUES (?, ?, ?, ?, ?)",
                    [
                        $attachment->id,
                        $newMailId,
                        $attachment->file_name,
                        $attachment->mime ?? '',
                        $attachment->updatedAt ?? now(),
                    ]
                );
            }
        }

        $this->info('    Attachments: '.count($attachments).' processed');
    }

    private function mergePivotTables(string $source, string $target, string $department): void
    {
        $this->info('  - Merging pivot tables...');

        $this->mergeIncomingMailService($source, $target, $department);
        $this->mergeIncomingMailRecipient($source, $target, $department);
    }

    private function mergeIncomingMailService(string $source, string $target, string $department): void
    {
        if (! $this->tableExists($source, 'courrier_service')) {
            return;
        }

        $pivots = DB::select("SELECT * FROM {$source}.courrier_service");
        $count = 0;

        foreach ($pivots as $pivot) {
            $newMailId = $this->idMappings[$department]['incoming_mails'][$pivot->courrier_id] ?? null;
            $newServiceId = $this->idMappings[$department]['services'][$pivot->service_id] ?? null;
            if ($newMailId === null) {
                continue;
            }
            if ($newServiceId === null) {
                continue;
            }

            $existing = DB::selectOne(
                "SELECT id FROM {$target}.incoming_mail_service WHERE incoming_mail_id = ? AND service_id = ?",
                [$newMailId, $newServiceId]
            );
            if ($existing) {
                continue;
            }

            if (! $this->dryRun) {
                DB::insert(
                    "INSERT INTO {$target}.incoming_mail_service (incoming_mail_id, service_id, is_primary) VALUES (?, ?, ?)",
                    [$newMailId, $newServiceId, true]
                );
            }
            $count++;
        }

        $this->info("    incoming_mail_service: {$count} processed");
    }

    private function mergeIncomingMailRecipient(string $source, string $target, string $department): void
    {
        if (! $this->tableExists($source, 'courrier_destinataire')) {
            return;
        }

        $pivots = DB::select("SELECT * FROM {$source}.courrier_destinataire");
        $count = 0;

        foreach ($pivots as $pivot) {
            $newMailId = $this->idMappings[$department]['incoming_mails'][$pivot->courrier_id] ?? null;
            $newRecipientId = $this->idMappings[$department]['recipients'][$pivot->destinataire_id] ?? null;
            if ($newMailId === null) {
                continue;
            }
            if ($newRecipientId === null) {
                continue;
            }

            $existing = DB::selectOne(
                "SELECT id FROM {$target}.incoming_mail_recipient WHERE incoming_mail_id = ? AND recipient_id = ?",
                [$newMailId, $newRecipientId]
            );
            if ($existing) {
                continue;
            }

            if (! $this->dryRun) {
                DB::insert(
                    "INSERT INTO {$target}.incoming_mail_recipient (incoming_mail_id, recipient_id, is_primary) VALUES (?, ?, ?)",
                    [$newMailId, $newRecipientId, $pivot->principal ?? false]
                );
            }
            $count++;
        }

        $this->info("    incoming_mail_recipient: {$count} processed");
    }

    private function updateExistingVilleRecords(string $target): void
    {
        $this->newLine();
        $this->info('Updating existing VILLE records with department...');

        $department = DepartmentCourrierEnum::VILLE->value;

        if (! $this->dryRun) {
            DB::update("UPDATE {$target}.incoming_mails SET department = ? WHERE department IS NULL", [$department]);
            DB::update("UPDATE {$target}.courrier_services SET department = ? WHERE department IS NULL", [$department]);
            DB::update("UPDATE {$target}.courrier_senders SET department = ? WHERE department IS NULL", [$department]);
        }

        $this->info('  Existing records updated with VILLE department');
    }

    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('=== MERGE SUMMARY ===');

        foreach ($this->idMappings as $department => $tables) {
            $this->info("{$department}:");
            foreach ($tables as $table => $mappings) {
                $this->line("  - {$table}: ".count($mappings).' records');
            }
        }
    }

    /**
     * Build a unique slug that fits the 70-character target columns.
     * The legacy slug is truncated and suffixed with the department and the
     * original id, which guarantees uniqueness across departments and against
     * existing VILLE records without overflowing the column.
     */
    private function makeSlug(string $base, string $department, int $oldId): string
    {
        $suffix = '-'.mb_strtolower($department).'-'.$oldId;

        return mb_substr($base, 0, 70 - mb_strlen($suffix)).$suffix;
    }

    /**
     * Normalize a legacy color value into a 7-character hex code (#rrggbb).
     * Legacy categories store either a hex code or an `rgb()/rgba()` string,
     * but the target `color` column only holds 7 characters.
     */
    private function normalizeColor(?string $color): string
    {
        $default = '#6b7280';

        if ($color === null || $color === '') {
            return $default;
        }

        $color = mb_trim($color);

        if (preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1) {
            return mb_strtolower($color);
        }

        if (preg_match('/rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/', $color, $matches) === 1) {
            return sprintf('#%02x%02x%02x', (int) $matches[1], (int) $matches[2], (int) $matches[3]);
        }

        return $default;
    }

    private function tableExists(string $database, string $table): bool
    {
        $result = DB::selectOne(
            'SELECT COUNT(*) AS total FROM information_schema.tables WHERE table_schema = ? AND table_name = ?',
            [$database, $table]
        );

        return $result !== null && (int) $result->total > 0;
    }
}
