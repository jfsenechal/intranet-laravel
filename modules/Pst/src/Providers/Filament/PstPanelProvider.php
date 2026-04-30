<?php

declare(strict_types=1);

namespace AcMarche\Pst\Providers\Filament;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\App\Traits\HooksTrait;
use AcMarche\App\Traits\PluginTrait;
use AcMarche\Security\Repository\UserRepository;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Platform;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class PstPanelProvider extends PanelProvider
{
    use HooksTrait;
    use PluginTrait;

    public function panel(Panel $panel): Panel
    {
        $path = $this->getPluginBasePath().'/../../';
        UserRepository::departmentSelected();

        return $panel
            ->id('pst-panel')
            ->path('pst')
            ->brandName('Pst')
            ->spa()
            ->resourceCreatePageRedirect('view')
            ->resourceEditPageRedirect('view')
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Slate,
                'secondary' => Color::Pink,
            ])
            ->databaseNotifications()
            ->unsavedChangesAlerts()

            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: $path.'Filament/Resources', for: 'AcMarche\\Pst\\Filament\\Resources')
            ->discoverPages(in: $path.'Filament/Pages', for: 'AcMarche\\Pst\\Filament\\Pages')
            ->pages([

            ])
            ->discoverWidgets(in: $path.'Filament/Widgets', for: 'AcMarche\\Pst\\Filament\\Widgets')
            ->pages([
                Dashboard::class,
            ])
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
            ])
            ->globalSearchFieldSuffix(fn (): ?string => match (Platform::detect()) {
                Platform::Windows, Platform::Linux => 'CTRL + K',
                Platform::Mac => '⌘ + K',
                default => null,
            })
            ->userMenuItems([
                Action::make('view-ville')
                    ->label('Ville')
                    ->url(fn (): string => route('select.department', ['department' => DepartmentEnum::VILLE->value]))
                    ->icon('tabler-switch')
                    ->visible(fn (): bool => count(auth()->user()->departments) > 1),
                Action::make('view-cpas')
                    ->label('Cpas')
                    ->url(fn (): string => route('select.department', ['department' => DepartmentEnum::CPAS->value]))
                    ->icon('tabler-switch')
                    ->visible(fn (): bool => count(auth()->user()->departments) > 1),
            ]);
    }
}
