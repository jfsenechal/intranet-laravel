<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    AcMarche\News\Providers\NewsPanelProvider::class,
    AcMarche\Document\Providers\DocumentPanelProvider::class,
    AcMarche\Security\Providers\SecurityPanelProvider::class,
    App\Providers\VoltServiceProvider::class,
];
