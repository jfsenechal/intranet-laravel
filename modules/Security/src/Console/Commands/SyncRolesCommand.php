<?php

declare(strict_types=1);

namespace AcMarche\Security\Console\Commands;

use AcMarche\Ad\Enums\RolesEnum as RoleEnumAd;
use AcMarche\Ad\Providers\AdServiceProvider;
use AcMarche\Agent\Enums\RolesEnum as RoleEnumAgent;
use AcMarche\Agent\Providers\AgentServiceProvider;
use AcMarche\AldermenAgenda\Enums\RolesEnum as RoleEnumAldermenAgenda;
use AcMarche\AldermenAgenda\Providers\AldermenAgendaServiceProvider;
use AcMarche\Courrier\Enums\RolesEnum as RoleEnumCourrier;
use AcMarche\Courrier\Providers\CourrierServiceProvider;
use AcMarche\Document\Enums\RolesEnum as RoleEnumDocument;
use AcMarche\Document\Providers\DocumentServiceProvider;
use AcMarche\Hrm\Enums\RolesEnum as RoleEnumHrm;
use AcMarche\Hrm\Providers\HrmServiceProvider;
use AcMarche\Mediation\Enums\RolesEnum as RoleEnumMediation;
use AcMarche\Mediation\Providers\MediationServiceProvider;
use AcMarche\Mileage\Enums\RolesEnum as RoleEnumMileage;
use AcMarche\Mileage\Providers\MileageServiceProvider;
use AcMarche\News\Enums\RolesEnum as RoleEnumNews;
use AcMarche\News\Providers\NewsServiceProvider;
use AcMarche\Offenses\Enums\RolesEnum as RoleEnumOffenses;
use AcMarche\Offenses\Providers\OffensesServiceProvider;
use AcMarche\Pst\Enums\RolesEnum as RoleEnumPst;
use AcMarche\Pst\Providers\PstServiceProvider;
use AcMarche\Publication\Enums\RolesEnum as RoleEnumPublication;
use AcMarche\Publication\Providers\PublicationServiceProvider;
use AcMarche\ResidentMeal\Enums\RolesEnum as RoleEnumResidentMeal;
use AcMarche\ResidentMeal\Providers\ResidentMealServiceProvider;
use AcMarche\Security\Enums\RolesEnum as RoleEnumSecurity;
use AcMarche\Security\Models\Role;
use AcMarche\Security\Providers\SecurityServiceProvider;
use Illuminate\Console\Command;
use Override;
use Symfony\Component\Console\Command\Command as SfCommand;

final class SyncRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    #[Override]
    protected $signature = 'intranet:sync-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    #[Override]
    protected $description = 'Sync roles with db and classes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $mappings = [
            [AgentServiceProvider::class, RoleEnumAgent::cases()],
            [AldermenAgendaServiceProvider::class, RoleEnumAldermenAgenda::cases()],
            [AdServiceProvider::class, RoleEnumAd::cases()],
            [CourrierServiceProvider::class, RoleEnumCourrier::cases()],
            [DocumentServiceProvider::class, RoleEnumDocument::cases()],
            [HrmServiceProvider::class, RoleEnumHrm::cases()],
            [MediationServiceProvider::class, RoleEnumMediation::cases()],
            [MileageServiceProvider::class, RoleEnumMileage::cases()],
            [NewsServiceProvider::class, RoleEnumNews::cases()],
            [OffensesServiceProvider::class, RoleEnumOffenses::cases()],
            [PstServiceProvider::class, RoleEnumPst::cases()],
            [PublicationServiceProvider::class, RoleEnumPublication::cases()],
            [ResidentMealServiceProvider::class, RoleEnumResidentMeal::cases()],
            [SecurityServiceProvider::class, RoleEnumSecurity::cases()],
        ];

        foreach ($mappings as [$providerClass, $cases]) {
            foreach ($cases as $role) {
                if (Role::query()->where('name', $role->value)->exists()) {
                    continue;
                }
                Role::create([
                    'module_id' => $providerClass::$module_id,
                    'name' => $role->value,
                ]);
                $this->info("Role {$role->value} created");
            }
        }

        return SfCommand::SUCCESS;
    }
}
