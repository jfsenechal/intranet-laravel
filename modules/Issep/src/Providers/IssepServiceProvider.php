<?php

declare(strict_types=1);

namespace AcMarche\Issep\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use AcMarche\Issep\Repository\IssepApiClient;
use AcMarche\Issep\Repository\StationRepository;
use Illuminate\Support\ServiceProvider;

final class IssepServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 48;

    public function register(): void
    {
        $this->registerModuleConfig();

        // Scoped rather than singleton: StationRepository memoizes the BelAQI indices it has
        // already fetched for the current page, which must not be carried between requests
        // by an Octane worker.
        $this->app->scoped(IssepApiClient::class, static fn (): IssepApiClient => IssepApiClient::fromConfig());
        $this->app->scoped(StationRepository::class);
    }

    public function boot(): void
    {
        $this->bootModule();
    }

    protected function moduleName(): string
    {
        return 'issep';
    }

    protected function modulePath(): string
    {
        return __DIR__.'/../..';
    }
}
