<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Providers\Filament;

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

final class OffensesPanelProvider extends PanelProvider
{
    use HooksTrait;
    use PluginTrait;

    public function panel(Panel $panel): Panel
    {
        $path = $this->getPluginBasePath().'/../../';

        return $panel
            ->id('offenses-panel')
            ->path('offenses')
            ->brandName('Sanctions administratives')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                'primary' => Color::Red,
            ])
            ->unsavedChangesAlerts()
            ->resourceCreatePageRedirect('view')
            ->resourceEditPageRedirect('view')
            ->databaseNotifications()
            ->discoverResources(in: $path.'Filament/Resources', for: 'AcMarche\\Offenses\\Filament\\Resources')
            ->discoverPages(in: $path.'Filament/Pages', for: 'AcMarche\\Offenses\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: $path.'Filament/Widgets', for: 'AcMarche\\Offenses\\Filament\\Widgets')
            ->widgets([])
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
