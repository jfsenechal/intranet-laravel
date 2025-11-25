<?php

declare(strict_types=1);

namespace AcMarche\Security\Console\Commands;

use App\Ldap\User as UserLdap;
use App\Models\User;
use Illuminate\Console\Command;
use Str;
use Symfony\Component\Console\Command\Command as SfCommand;

final class SyncUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intranet:sync-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users with ldap';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // $this->agentRole = Role::where('name', RoleEnum::AGENT->value)->first();

        foreach (UserLdap::all() as $userLdap) {
            if (!$userLdap->getFirstAttribute('mail')) {
                continue;
            }
            if (!$this->isActive($userLdap)) {
                continue;
            }
            $username = $userLdap->getFirstAttribute('samaccountname');
            if (!$user = User::where('username', $username)->first()) {
                //  $this->addUser($username, $userLdap);
            } else {
                $this->updateUser($user, $userLdap);
            }
        }

        $this->removeOldUsers();

        return SfCommand::SUCCESS;
    }

    private function addUser(string $username, UserLdap $userLdap): void
    {
        $data = $this->data($userLdap, $username);
        $data['username'] = $username;
        $data['password'] = Str::password();
        $user = User::create($data);
        $user->addRole($this->agentRole);
        $this->info('Add '.$user->first_name.' '.$user->last_name);
    }

    private function updateUser(User $user, mixed $userLdap): void
    {
        $user->update(User::generateDataFromLdap($userLdap, $user->username));
    }

    private function removeOldUsers(): void
    {
        $ldapUsernames = [];

        foreach (UserLdap::all() as $userLdap) {
            $ldapUsernames[] = $userLdap->getFirstAttribute('samaccountname');
        }

        if (count($ldapUsernames) > 200) {
            foreach (User::all() as $user) {
                if (!in_array($user->username, $ldapUsernames)) {
                    $user->delete();
                    $this->info('Removed from pst'.$user->first_name.' '.$user->last_name);
                }
            }
        }
    }

    private function isActive(UserLdap $userLdap): bool
    {
        return $userLdap->getFirstAttribute('userAccountControl') !== 66050;
    }
}
