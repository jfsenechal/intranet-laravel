<?php

namespace AcMarche\Courrier\Providers;

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
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class CourrierPanelProvider extends PanelProvider
{
    use PluginTrait;

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('courrier-panel')
            ->path('indicateur')
            ->brandName('Indicateur')
            ->colors([
                'primary' => Color::Pink,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->unsavedChangesAlerts()
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Courrier/Resources'), for: 'App\Filament\Courrier\Resources')
            ->discoverPages(in: app_path('Filament/Courrier/Pages'), for: 'App\Filament\Courrier\Pages')
            ->pages([

            ])
            ->discoverWidgets(in: app_path('Filament/Courrier/Widgets'), for: 'App\Filament\Courrier\Widgets')
            ->widgets([

            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
