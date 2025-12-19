<?php

namespace AcMarche\Publication\Providers;

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

class PublicationPanelProvider extends PanelProvider
{
    use PluginTrait;

    public function panel(Panel $panel): Panel
    {
        $path = $this->getPluginBasePath().'/../';

        return $panel
            ->id('publication-panel')
            ->path('publication')
            ->brandName('Publications communales')
            ->colors([
                'primary' => Color::Pink,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->unsavedChangesAlerts()

            ->discoverResources(in: $path.'Filament/Resources', for: 'AcMarche\\Publication\\Filament\\Resources')
            ->discoverPages(in: $path.'Filament/Pages', for: 'AcMarche\\Publication\\Filament\\Pages')
            ->pages([

            ])
            ->discoverWidgets(in: $path.'Filament/Widgets', for: 'AcMarche\\Publication\\Filament\\Widgets')
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
