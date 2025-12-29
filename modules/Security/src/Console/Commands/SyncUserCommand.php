<?php

declare(strict_types=1);

namespace AcMarche\Security\Console\Commands;

use AcMarche\Security\Ldap\User as UserLdap;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
            if (! $userLdap->getFirstAttribute('mail')) {
                continue;
            }
            if (! $this->isActive($userLdap)) {
                continue;
            }
            $username = $userLdap->getFirstAttribute('samaccountname');
            if (! $user = User::where('username', $username)->first()) {
                $this->addUser($username, $userLdap);
            } else {
                $this->updateUser($user, $userLdap);
            }
        }

        $this->removeOldUsers();

        return SfCommand::SUCCESS;
    }

    private function addUser(string $username, UserLdap $userLdap): void
    {
        $data = User::generateDataFromLdap($userLdap);
        $data['username'] = $username;
        $data['password'] = Str::password();
        $user = User::create($data);
        // $user->addRole('ROLE_ADMIN');
        $this->info('Add '.$user->first_name.' '.$user->last_name);
    }

    private function updateUser(User $user, UserLdap $userLdap): void
    {
        $user->update(User::generateDataFromLdap($userLdap));
    }

    private function removeOldUsers(): void
    {
        $ldapUsernames = [];

        foreach (UserLdap::all() as $userLdap) {
            $ldapUsernames[] = $userLdap->getFirstAttribute('samaccountname');
        }

        if (count($ldapUsernames) > 200) {
            foreach (User::all() as $user) {
                if (! in_array($user->username, $ldapUsernames)) {
                    $profile = DB::connection('mariadb')
                        ->table('profiles')
                        ->where('user_id', $user->id)
                        ->first();

                    if ($profile) {
                        DB::connection('mariadb')
                            ->table('profiles')
                            ->where('user_id', $user->id)
                            ->delete();
                    }
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
