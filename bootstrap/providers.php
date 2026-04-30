<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    AcMarche\Ad\Providers\Filament\AdPanelProvider::class,
    AcMarche\App\Providers\Filament\AppPanelProvider::class,
    AcMarche\Security\Providers\Filament\SecurityPanelProvider::class,
    AcMarche\MailingList\Providers\Filament\MailingListPanelProvider::class,
    AcMarche\Pst\Providers\Filament\PstPanelProvider::class,
    AcMarche\Courrier\Providers\Filament\CourrierPanelProvider::class,
    AcMarche\Document\Providers\Filament\DocumentPanelProvider::class,
    AcMarche\Hrm\Providers\Filament\HrmPanelProvider::class,
    AcMarche\Mileage\Providers\Filament\MileagePanelProvider::class,
    AcMarche\News\Providers\Filament\NewsPanelProvider::class,
    AcMarche\Publication\Providers\Filament\PublicationPanelProvider::class,
    AcMarche\Agent\Providers\Filament\AgentPanelProvider::class,
    AcMarche\WhoIsWho\Providers\Filament\WhoIsWhoPanelProvider::class,
    AcMarche\QrCode\Providers\Filament\QrCodePanelProvider::class,
];
