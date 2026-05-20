<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Providers;

use AcMarche\ActivityManager\Models\Activity;
use AcMarche\ActivityManager\Models\Schedule;
use AcMarche\ActivityManager\Models\Member;
use AcMarche\ActivityManager\Policies\ActivityPolicy;
use AcMarche\ActivityManager\Policies\SchedulePolicy;
use AcMarche\ActivityManager\Policies\MemberPolicy;
use AcMarche\App\Traits\ModuleServiceProviderTrait;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class ActivityManagerServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 41;

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

        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(Schedule::class, SchedulePolicy::class);
        Gate::policy(Member::class, MemberPolicy::class);
    }

    protected function moduleName(): string
    {
        return 'activity-manager';
    }
}
