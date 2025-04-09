<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    AcMarche\Support\Providers\SupportServiceProvider::class,
    AcMarche\Support\Providers\SupportPanelProvider::class,
    AcMarche\Security\Providers\SecurityServiceProvider::class,
    AcMarche\Security\Providers\SecurityPanelProvider::class,
    AcMarche\News\Providers\NewsServiceProvider::class,
    AcMarche\News\Providers\NewsPanelProvider::class,
];
