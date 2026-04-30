<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use AcMarche\Pst\Policies\RegisterPolicies;
use Illuminate\Support\ServiceProvider;

final class MailingListServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 61;

    public function register(): void
    {
        $this->registerModuleConfig();
    }

    public function boot(): void
    {
        RegisterPolicies::register();
        $this->bootModule();
    }

    protected function moduleName(): string
    {
        return 'mailing-list';
    }
}
