<?php

declare(strict_types=1);

namespace AcMarche\KnowledgeBase\Providers;

use AcMarche\App\Traits\ModuleServiceProviderTrait;
use AcMarche\KnowledgeBase\Documentation\Documentation;
use AcMarche\KnowledgeBase\Documentation\ImageStore;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

final class KnowledgeBaseServiceProvider extends ServiceProvider
{
    use ModuleServiceProviderTrait;

    public static int $module_id = 65;

    public function register(): void
    {
        $this->registerModuleConfig();

        $this->app->scoped(Documentation::class, fn (): Documentation => new Documentation(Storage::disk('knowledge-base')));
        $this->app->scoped(ImageStore::class, fn (): ImageStore => new ImageStore(Storage::disk('knowledge-base')));
    }

    public function boot(): void
    {
        $this->bootModule();
    }

    protected function moduleName(): string
    {
        return 'knowledge-base';
    }
}
