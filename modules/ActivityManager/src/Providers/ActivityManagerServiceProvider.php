<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Providers;

use AcMarche\ActivityManager\Models\Activite;
use AcMarche\ActivityManager\Models\Cours;
use AcMarche\ActivityManager\Models\Membre;
use AcMarche\ActivityManager\Policies\ActivitePolicy;
use AcMarche\ActivityManager\Policies\CoursPolicy;
use AcMarche\ActivityManager\Policies\MembrePolicy;
use AcMarche\App\Traits\ModuleServiceProviderTrait;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class ActivityManagerServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 63;

    public function register(): void
    {
        $this->registerModuleConfig();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([

            ]);
        }
        $this->bootModule();

        Gate::policy(Activite::class, ActivitePolicy::class);
        Gate::policy(Cours::class, CoursPolicy::class);
        Gate::policy(Membre::class, MembrePolicy::class);
    }

    protected function moduleName(): string
    {
        return 'activity-manager';
    }
}
