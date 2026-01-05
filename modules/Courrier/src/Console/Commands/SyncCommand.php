<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Console\Commands;

use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use AcMarche\Security\Ldap\User as UserLdap;
use Illuminate\Console\Command;
use LdapRecord\Models\Model;

final class SyncCommand extends Command
{
    protected $signature = 'courrier:sync {--dry-run : Run without making changes}';

    protected $description = 'Sync recipients with users ldap';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info('Starting LDAP sync...');

        $this->cleanRecipients($dryRun);

        $employes = UserLdap::all();

        if ($employes->count() === 0) {
            $this->warn('No LDAP users found.');

            return self::SUCCESS;
        }

        $this->info("Found {$employes->count()} LDAP users.");

        foreach ($employes as $employe) {
            $this->syncRecipient($employe, $dryRun);
        }

        $this->syncServiceMembers($dryRun);

        $this->info('Sync completed.');

        return self::SUCCESS;
    }

    protected function syncRecipient(Model $model, bool $dryRun): void
    {
        $email = $model->getFirstAttribute('mail');
        $lastName = $model->getFirstAttribute('sn');
        $firstName = $model->getFirstAttribute('givenName');
        $username = $model->getFirstAttribute('sAMAccountName');

        if (! $email) {
            return;
        }

        if (str_contains((string) $username, 'stage')) {
            return;
        }

        $recipient = Recipient::query()->where('username', $username)->first();

        if ($recipient instanceof Recipient) {
            if (! $dryRun) {
                $recipient->update([
                    'last_name' => $lastName,
                    'first_name' => $firstName,
                    'email' => $email,
                ]);
            }
            $this->line("Updated recipient: {$firstName} {$lastName}");
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (! $dryRun) {
                $recipient = Recipient::create([
                    'last_name' => $lastName,
                    'first_name' => $firstName,
                    'username' => $username,
                    'email' => $email,
                    'is_active' => true,
                ]);
            }
            $this->line("Created recipient: {$firstName} {$lastName}");
        }

        if ($recipient instanceof Recipient && ! $this->isActive($model)) {
            if (! $dryRun) {
                $recipient->update([
                    'is_active' => false,
                    'email' => 'noemail@marche.be',
                ]);
            }
            $this->line("Deactivated recipient: {$firstName} {$lastName}");
        }
    }

    private function isActive(Model $model): bool
    {
        return $model->getFirstAttribute('userAccountControl') !== 66050;
    }

    private function syncServiceMembers(bool $dryRun): void
    {
        $this->info('Syncing service members...');

        $services = Service::with('recipients')->get();

        foreach ($services as $service) {
            foreach ($service->recipients as $recipient) {
                $entry = UserLdap::query()->where('sAMAccountName', $recipient->username)->first();

                if (! $entry instanceof Model || ! $this->isActive($entry)) {
                    if (! $dryRun) {
                        $service->recipients()->detach($recipient->id);
                    }
                    $this->line("Removed {$recipient->full_name} from service {$service->name}");
                }
            }
        }
    }

    private function cleanRecipients(bool $dryRun): void
    {
        $this->info('Cleaning recipients not in LDAP...');

        $employes = UserLdap::all();
        $ldapUsernames = $employes->map(fn (Model $e) => $e->getFirstAttribute('sAMAccountName'))->filter()->toArray();

        if (count($ldapUsernames) <= 100) {
            $this->warn('Not enough LDAP users found, skipping cleanup for safety.');

            return;
        }

        $recipientsToRemove = Recipient::query()
            ->whereNotIn('username', $ldapUsernames)
            ->get();

        foreach ($recipientsToRemove as $recipient) {
            if (! $dryRun) {
                $recipient->incomingMails()->detach();
                $recipient->services()->detach();
                $recipient->delete();
            }
            $this->line("Removed recipient: {$recipient->full_name}");
        }
    }
}
