<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Console\Commands;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class MergeCommand extends Command
{
    protected $signature = 'courrier:merge
        {--dry-run : Run without making changes}
        {--target=indicateur_ville : Target database name}';

    protected $description = 'Merge indicateur_cpas and indicateur_bgm databases into the target database with department field';

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

        if (! $this->confirm('This will merge data from CPAS and BGM databases into the target. Continue?')) {
            $this->info('Operation cancelled.');

            return self::SUCCESS;
        }

        foreach ($this->sourceConfigs as $department => $sourceDatabase) {
            $this->info("Processing {$department} from {$sourceDatabase}...");
            $this->newLine();

            try {
                $this->mergeDatabase($sourceDatabase, $targetDatabase, $department);
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
        $this->info('  - Merging categories...');

        $categories = DB::select("SELECT * FROM {$source}.categories");

        foreach ($categories as $category) {
            $oldId = $category->id;

            $existing = DB::selectOne(
                "SELECT id FROM {$target}.categories WHERE name = ?",
                [$category->name]
            );

            if ($existing) {
                $this->idMappings[$department]['categories'][$oldId] = $existing->id;
                $this->line("    Skipping duplicate category: {$category->name}");

                continue;
            }

            if (! $this->dryRun) {
                DB::insert(
                    "INSERT INTO {$target}.categories (name, color, created_at, updated_at) VALUES (?, ?, ?, ?)",
                    [$category->name, $category->color ?? '#6b7280', $category->created_at, $category->updated_at]
                );
                $newId = DB::getPdo()->lastInsertId();
                $this->idMappings[$department]['categories'][$oldId] = $newId;
            }
        }

        $this->info('    Categories: '.count($categories).' processed');
    }

    private function mergeServices(string $source, string $target, string $department): void
    {
        $this->info('  - Merging services...');

        $services = DB::select("SELECT * FROM {$source}.services");

        foreach ($services as $service) {
            $oldId = $service->id;
            $slugField = property_exists($service, 'slugname') ? 'slugname' : 'slug';
            $slug = $service->$slugField ?? $service->slug ?? null;

            if (! $this->dryRun) {
                DB::insert(
                    "INSERT INTO {$target}.services (slugname, name, initials, is_active, department) VALUES (?, ?, ?, ?, ?)",
                    [
                        $slug.'-'.$department,
                        $service->name,
                        $service->initials,
                        $service->is_active,
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
        $this->info('  - Merging senders...');

        $senders = DB::select("SELECT * FROM {$source}.senders");

        foreach ($senders as $sender) {
            $oldId = $sender->id;

            if (! $this->dryRun) {
                DB::insert(
                    "INSERT INTO {$target}.senders (slug, name, department) VALUES (?, ?, ?)",
                    [
                        $sender->slug.'-'.$department,
                        $sender->name,
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
        $this->info('  - Merging recipients...');

        $recipients = DB::select("SELECT * FROM {$source}.recipients ORDER BY supervisor_id ASC NULLS FIRST");

        foreach ($recipients as $recipient) {
            $oldId = $recipient->id;

            $newSupervisorId = null;
            if ($recipient->supervisor_id !== null) {
                $newSupervisorId = $this->idMappings[$department]['recipients'][$recipient->supervisor_id] ?? null;
            }

            if (! $this->dryRun) {
                DB::insert(
                    "INSERT INTO {$target}.recipients (supervisor_id, slug, last_name, first_name, username, email, is_active, receives_attachments) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $newSupervisorId,
                        $recipient->slug.'-'.$department,
                        $recipient->last_name,
                        $recipient->first_name,
                        $recipient->username,
                        $recipient->email,
                        $recipient->is_active,
                        $recipient->receives_attachments,
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
        $this->info('  - Merging incoming mails...');

        $mails = DB::select("SELECT * FROM {$source}.incoming_mails");

        foreach ($mails as $mail) {
            $oldId = $mail->id;

            $newCategoryId = null;
            if ($mail->category_id !== null) {
                $newCategoryId = $this->idMappings[$department]['categories'][$mail->category_id] ?? null;
            }

            if (! $this->dryRun) {
                DB::insert(
                    "INSERT INTO {$target}.incoming_mails
                    (category_id, reference_number, sender, description, mail_date, is_notified, is_registered, has_acknowledgment, user_add, created_at, updated_at, file_size, file_mime, deleted_at, department)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $newCategoryId,
                        $mail->reference_number,
                        $mail->sender,
                        $mail->description,
                        $mail->mail_date,
                        $mail->is_notified,
                        $mail->is_registered,
                        $mail->has_acknowledgment,
                        $mail->user_add,
                        $mail->created_at,
                        $mail->updated_at,
                        $mail->file_size ?? null,
                        $mail->file_mime ?? null,
                        $mail->deleted_at ?? null,
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
        $this->info('  - Merging attachments...');

        $attachments = DB::select("SELECT * FROM {$source}.attachments");

        foreach ($attachments as $attachment) {
            $newMailId = $this->idMappings[$department]['incoming_mails'][$attachment->incoming_mail_id] ?? null;

            if ($newMailId === null) {
                $this->warn("    Skipping orphan attachment: {$attachment->file_name}");

                continue;
            }

            if (! $this->dryRun) {
                DB::insert(
                    "INSERT INTO {$target}.attachments (incoming_mail_id, file_name, mime, created_at) VALUES (?, ?, ?, ?)",
                    [
                        $newMailId,
                        $attachment->file_name,
                        $attachment->mime ?? null,
                        $attachment->created_at ?? now(),
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
        $this->mergeRecipientService($source, $target, $department);
    }

    private function mergeIncomingMailService(string $source, string $target, string $department): void
    {
        $pivots = DB::select("SELECT * FROM {$source}.incoming_mail_service");
        $count = 0;

        foreach ($pivots as $pivot) {
            $newMailId = $this->idMappings[$department]['incoming_mails'][$pivot->incoming_mail_id] ?? null;
            $newServiceId = $this->idMappings[$department]['services'][$pivot->service_id] ?? null;

            if ($newMailId === null || $newServiceId === null) {
                continue;
            }

            if (! $this->dryRun) {
                DB::insert(
                    "INSERT INTO {$target}.incoming_mail_service (incoming_mail_id, service_id, is_primary) VALUES (?, ?, ?)",
                    [$newMailId, $newServiceId, $pivot->is_primary ?? false]
                );
            }
            $count++;
        }

        $this->info("    incoming_mail_service: {$count} processed");
    }

    private function mergeIncomingMailRecipient(string $source, string $target, string $department): void
    {
        $pivots = DB::select("SELECT * FROM {$source}.incoming_mail_recipient");
        $count = 0;

        foreach ($pivots as $pivot) {
            $newMailId = $this->idMappings[$department]['incoming_mails'][$pivot->incoming_mail_id] ?? null;
            $newRecipientId = $this->idMappings[$department]['recipients'][$pivot->recipient_id] ?? null;

            if ($newMailId === null || $newRecipientId === null) {
                continue;
            }

            if (! $this->dryRun) {
                DB::insert(
                    "INSERT INTO {$target}.incoming_mail_recipient (incoming_mail_id, recipient_id, is_primary) VALUES (?, ?, ?)",
                    [$newMailId, $newRecipientId, $pivot->is_primary ?? false]
                );
            }
            $count++;
        }

        $this->info("    incoming_mail_recipient: {$count} processed");
    }

    private function mergeRecipientService(string $source, string $target, string $department): void
    {
        $pivots = DB::select("SELECT * FROM {$source}.recipient_service");
        $count = 0;

        foreach ($pivots as $pivot) {
            $newRecipientId = $this->idMappings[$department]['recipients'][$pivot->recipient_id] ?? null;
            $newServiceId = $this->idMappings[$department]['services'][$pivot->service_id] ?? null;

            if ($newRecipientId === null || $newServiceId === null) {
                continue;
            }

            if (! $this->dryRun) {
                DB::insert(
                    "INSERT INTO {$target}.recipient_service (recipient_id, service_id) VALUES (?, ?)",
                    [$newRecipientId, $newServiceId]
                );
            }
            $count++;
        }

        $this->info("    recipient_service: {$count} processed");
    }

    private function updateExistingVilleRecords(string $target): void
    {
        $this->newLine();
        $this->info('Updating existing VILLE records with department...');

        $department = DepartmentCourrierEnum::VILLE->value;

        if (! $this->dryRun) {
            DB::update("UPDATE {$target}.incoming_mails SET department = ? WHERE department IS NULL", [$department]);
            DB::update("UPDATE {$target}.services SET department = ? WHERE department IS NULL", [$department]);
            DB::update("UPDATE {$target}.senders SET department = ? WHERE department IS NULL", [$department]);
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
}
