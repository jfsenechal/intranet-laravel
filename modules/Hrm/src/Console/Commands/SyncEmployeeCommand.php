<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Console\Commands;

use AcMarche\Hrm\Models\Employee;
use AcMarche\Security\Repository\LdapRepository;
use Illuminate\Console\Command;
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
}
