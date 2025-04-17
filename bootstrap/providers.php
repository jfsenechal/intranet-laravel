<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    AcMarche\App\Providers\AppServiceProvider::class,
    AcMarche\App\Providers\AppPanelProvider::class,
    AcMarche\Security\Providers\SecurityServiceProvider::class,
    AcMarche\Security\Providers\SecurityPanelProvider::class,
    AcMarche\News\Providers\NewsServiceProvider::class,
    AcMarche\News\Providers\NewsPanelProvider::class,
    AcMarche\Document\Providers\DocumentServiceProvider::class,
    AcMarche\Document\Providers\DocumentPanelProvider::class,
];
