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
use AcMarche\News\Enums\RolesEnum as RoleEnumNews;
use AcMarche\News\Providers\NewsServiceProvider;
use AcMarche\Security\Models\Role;
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
            [NewsServiceProvider::class, RoleEnumNews::cases()],
            [AgentServiceProvider::class, RoleEnumAgent::cases()],
            [AldermenAgendaServiceProvider::class, RoleEnumAldermenAgenda::cases()],
            [AdServiceProvider::class, RoleEnumAd::cases()],
            [CourrierServiceProvider::class, RoleEnumCourrier::cases()],
            [DocumentServiceProvider::class, RoleEnumDocument::cases()],
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
