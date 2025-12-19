<?php

namespace AcMarche\Document\Providers;

use AcMarche\App\Traits\PluginTrait;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class DocumentPanelProvider extends PanelProvider
{
    use PluginTrait;

    public function panel(Panel $panel): Panel
    {
        $path = $this->getPluginBasePath().'/../';

        return $panel
            ->id('document-panel')
            ->path('document')
            ->brandName('Documents utiles')
            ->colors([
                'primary' => Color::Pink,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->unsavedChangesAlerts()

            ->discoverResources(in: $path.'Filament/Resources', for: 'AcMarche\\Document\\Filament\\Resources')
            ->discoverPages(in: $path.'Filament/Pages', for: 'AcMarche\\Document\\Filament\\Pages')
            ->pages([

            ])
            ->discoverWidgets(in: $path.'Filament/Widgets', for: 'AcMarche\\Document\\Filament\\Widgets')
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
