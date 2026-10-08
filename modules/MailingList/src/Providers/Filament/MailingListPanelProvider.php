<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Providers\Filament;

use AcMarche\App\Traits\HooksTrait;
use AcMarche\App\Traits\PluginTrait;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class MailingListPanelProvider extends PanelProvider
{
    use HooksTrait;
    use PluginTrait;

    public function panel(Panel $panel): Panel
    {
        $path = $this->getPluginBasePath().'/../../';

        return $panel
            ->id('mailing-list-panel')
            ->path('mailing-list')
            ->brandName('Carnets et liste de diffusion')
            ->font('Instrument Sans')
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->unsavedChangesAlerts()
            ->resourceCreatePageRedirect('view')
            ->resourceEditPageRedirect('view')
            ->databaseNotifications()
            ->discoverResources(in: $path.'Filament/Resources', for: 'AcMarche\\MailingList\\Filament\\Resources')
            ->discoverPages(in: $path.'Filament/Pages', for: 'AcMarche\\MailingList\\Filament\\Pages')
            ->pages([

            ])
            ->discoverWidgets(in: $path.'Filament/Widgets', for: 'AcMarche\\MailingList\\Filament\\Widgets')
            ->widgets([

            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
