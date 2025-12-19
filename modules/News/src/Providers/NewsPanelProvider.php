<?php

namespace AcMarche\News\Providers;

use AcMarche\App\Traits\PluginTrait;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class NewsPanelProvider extends PanelProvider
{
    use PluginTrait;

    public function panel(Panel $panel): Panel
    {
        $path = $this->getPluginBasePath().'/../';

        return $panel
            ->id('news')
            ->path('news')
            ->colors([
                'primary' => Color::Pink,
            ])
            ->brandName('Quoi de neuf?')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->unsavedChangesAlerts()

            ->discoverResources(in: $path.'Filament/Resources', for: 'AcMarche\\News\\Filament\\Resources')
            ->discoverPages(in: $path.'Filament/Pages', for: 'AcMarche\\News\\Filament\\Pages')
            ->pages([

            ])
            ->discoverWidgets(in: $path.'Filament/Widgets', for: 'AcMarche\\News\\Filament\\Widgets')
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
