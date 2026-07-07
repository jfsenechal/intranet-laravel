<?php

declare(strict_types=1);

namespace AcMarche\News\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use AcMarche\News\Events\NewsProcessed;
use AcMarche\News\Listeners\NewsNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class NewsServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 15;

    public function register(): void
    {
        $this->registerModuleConfig();
    }

    public function boot(): void
    {
        $this->bootModule();

        Event::listen(NewsProcessed::class, NewsNotification::class);
    }

    protected function moduleName(): string
    {
        return 'news';
    }

    protected function modulePath(): string
    {
        return __DIR__.'/../..';
    }
}
