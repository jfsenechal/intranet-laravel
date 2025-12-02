<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    AcMarche\Document\Providers\DocumentPanelProvider::class,
    AcMarche\News\Providers\NewsPanelProvider::class,
    AcMarche\Mileage\Providers\MileagePanelProvider::class,
    AcMarche\Publication\Providers\PublicationPanelProvider::class,
    AcMarche\Security\Providers\SecurityPanelProvider::class,
    App\Providers\VoltServiceProvider::class,
];
