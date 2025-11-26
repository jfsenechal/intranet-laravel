<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    AcMarche\News\Providers\NewsPanelProvider::class,
    AcMarche\Document\Providers\DocumentPanelProvider::class,
    AcMarche\Mileage\Providers\MileagePanelProvider::class,
    AcMarche\Security\Providers\SecurityPanelProvider::class,
    AcMarche\Publication\Providers\PublicationPanelProvider::class,
    App\Providers\VoltServiceProvider::class,
];
