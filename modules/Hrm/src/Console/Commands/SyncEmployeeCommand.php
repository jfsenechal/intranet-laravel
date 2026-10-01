<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Console\Commands;

use AcMarche\Agent\Models\Profile;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Security\Repository\LdapRepository;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use LdapRecord\Models\Model;
use Symfony\Component\Console\Command\Command as SfCommand;

final class SyncEmployeeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hrm:sync-employees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync employees with ldap';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $linked = $this->syncUsernamesFromProfiles();

        foreach (Employee::query()->whereNotNull('username')->cursor() as $employee) {
            $model = LdapRepository::findByUsername($employee->username);
            if (! $model instanceof Model) {
                $employee->professional_email = null;
                $employee->professional_mobile = null;
                $employee->professional_phone = null;
                $employee->professional_phone_extension = null;
            } else {
                $employee->professional_email = $model->getFirstAttribute('mail');
                $employee->professional_mobile = $model->getFirstAttribute('mobile');
                $employee->professional_phone = $model->getFirstAttribute('telephoneNumber');
                $employee->professional_phone_extension = $model->getFirstAttribute('ipPhone');
            }

            $employee->save();
        }

        return SfCommand::SUCCESS;
    }

    /**
     * The employees table has no writer for its own `username`: the link to the LDAP
     * account is maintained on the agent Profile, set once at create through the LDAP
     * picker. Copy it onto the matching employee before the LDAP pass below, so agents
     * linked since the last run are synced too.
     *
     * Profiles live on `maria-agent` and employees on `maria-hrm`, so the two sides are
     * matched in PHP rather than with a join.
     *
     * @return int the number of employees whose username changed
     */
    private function syncUsernamesFromProfiles(): int
    {
        $linked = 0;

        Profile::query()
            ->whereNotNull('employee_id')
            ->whereNotNull('username')
            ->select(['id', 'employee_id', 'username'])
            ->chunkById(500, function (Collection $profiles) use (&$linked): void {
                /** @var Collection<int, Employee> $employees */
                $employees = Employee::query()
                    ->whereIn('id', $profiles->pluck('employee_id')->all())
                    ->get()
                    ->keyBy('id');

                foreach ($profiles as $profile) {
                    $employee = $employees->get($profile->employee_id);

                    if (! $employee instanceof Employee || $employee->username === $profile->username) {
                        continue;
                    }

                    $employee->username = $profile->username;
                    $employee->save();
                    $linked++;
                }
            });

        return $linked;
    }
}
