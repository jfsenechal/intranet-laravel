<?php

declare(strict_types=1);

namespace AcMarche\Issep\Providers\Filament;

use AcMarche\App\Traits\HooksTrait;
use AcMarche\App\Traits\PluginTrait;
use AcMarche\Issep\Enums\IndiceEnum;
use AcMarche\Issep\Http\Middleware\EnsureCapteurAccess;
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

final class IssepPanelProvider extends PanelProvider
{
    use HooksTrait;
    use PluginTrait;

    public function panel(Panel $panel): Panel
    {
        $path = $this->getPluginBasePath().'/../../';

        return $panel
            ->id('issep-panel')
            ->path('issep')
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Teal,
                // The BelAQI scale, so a badge can be coloured by index name.
                ...IndiceEnum::panelColors(),
            ])
            ->brandName('Capteurs d\'air')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->unsavedChangesAlerts()
            ->resourceCreatePageRedirect('view')
            ->resourceEditPageRedirect('view')
            ->discoverResources(in: $path.'Filament/Resources', for: 'AcMarche\\Issep\\Filament\\Resources')
            ->discoverPages(in: $path.'Filament/Pages', for: 'AcMarche\\Issep\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: $path.'Filament/Widgets', for: 'AcMarche\\Issep\\Filament\\Widgets')
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
                EnsureCapteurAccess::class,
            ]);
    }
}
