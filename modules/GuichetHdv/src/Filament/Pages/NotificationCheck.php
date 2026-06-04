<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Pages;

use AcMarche\GuichetHdv\Filament\Concerns\InteractsWithWebPush;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Override;
use UnitEnum;

final class NotificationCheck extends Page
{
    use InteractsWithWebPush;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = Heroicon::BellAlert;

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Paramètres';

    #[Override]
    protected static ?string $navigationLabel = 'Notifications';

    #[Override]
    protected static ?int $navigationSort = 1;

    #[Override]
    protected string $view = 'guichet-hdv::filament.pages.notification-check';

    public function getTitle(): string
    {
        return 'Notifications du navigateur';
    }
}
