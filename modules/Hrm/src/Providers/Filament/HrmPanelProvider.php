<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Providers\Filament;

use AcMarche\App\Traits\HooksTrait;
use AcMarche\App\Traits\PluginTrait;
use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\ListEmployees;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class HrmPanelProvider extends PanelProvider
{
    use HooksTrait;
    use PluginTrait;

    public function panel(Panel $panel): Panel
    {
        $path = $this->getPluginBasePath().'/../../';

        return $panel
            ->id('hrm-panel')
            ->path('hrm')
            ->brandName('Resources Humaines')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->unsavedChangesAlerts()
            ->resourceCreatePageRedirect('view')
            ->resourceEditPageRedirect('view')
            ->databaseNotifications()
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Personnel')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Listing'),
                NavigationGroup::make()
                    ->label('Configuration')
                    ->collapsed(),
            ])
            ->discoverResources(in: $path.'Filament/Resources', for: 'AcMarche\\Hrm\\Filament\\Resources')
            ->discoverPages(in: $path.'Filament/Pages', for: 'AcMarche\\Hrm\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: $path.'Filament/Widgets', for: 'AcMarche\\Hrm\\Filament\\Widgets')
            ->widgets([])
            ->navigationItems([
                NavigationItem::make(StatusEnum::APPLICATION->value)
                    ->label('Candidats')
                    ->icon(Heroicon::OutlinedUserPlus)
                    ->group('Personnel')
                    ->sort(2)
                    ->url(fn (): string => ListEmployees::getUrl(parameters: [
                        'filters' => ['status' => ['value' => StatusEnum::APPLICATION->value]],
                    ])),
                NavigationItem::make(StatusEnum::STUDENT->value)
                    ->label('Etudiants')
                    ->icon(Heroicon::OutlinedAcademicCap)
                    ->group('Personnel')
                    ->sort(3)
                    ->url(fn (): string => ListEmployees::getUrl(parameters: [
                        'filters' => ['status' => ['value' => StatusEnum::STUDENT->value]],
                    ])),
                NavigationItem::make(StatusEnum::INTERN->value)
                    ->label('Stagiaires')
                    ->icon(Heroicon::OutlinedBriefcase)
                    ->group('Personnel')
                    ->sort(4)
                    ->url(fn (): string => ListEmployees::getUrl(parameters: [
                        'filters' => ['status' => ['value' => StatusEnum::INTERN->value]],
                    ])),
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
