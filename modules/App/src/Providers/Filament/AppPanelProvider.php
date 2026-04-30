<?php

declare(strict_types=1);

namespace AcMarche\App\Providers\Filament;

use AcMarche\App\Filament\Pages\DashboardPage;
use AcMarche\App\Filament\Pages\TeleworkPage;
use AcMarche\App\Traits\HooksTrait;
use AcMarche\App\Traits\PluginTrait;
use App\Filament\Pages\Auth\Login;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class AppPanelProvider extends PanelProvider
{
    use HooksTrait;
    use PluginTrait;

    public function panel(Panel $panel): Panel
    {
        $path = $this->getPluginBasePath().'/../../';

        return $panel
            ->default()
            ->id('app-panel')
            ->login(Login::class)
            ->path('app')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->spa()
            ->profile()
            ->multiFactorAuthentication(
                AppAuthentication::make()
                    ->recoverable(),
            )
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications()
            ->unsavedChangesAlerts()
            ->resourceCreatePageRedirect('view')
            ->resourceEditPageRedirect('view')
            ->maxContentWidth(Width::Full)
            ->discoverResources(in: $path.'Filament/Resources', for: 'AcMarche\\App\\Filament\\Resources')
            ->discoverPages(in: $path.'Filament/Pages', for: 'AcMarche\\App\\Filament\\Pages')
            ->pages([
                DashboardPage::class,
                TeleworkPage::class,
            ])
            ->discoverWidgets(in: $path.'Filament/Widgets', for: 'AcMarche\\App\\Filament\\Widgets')
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
