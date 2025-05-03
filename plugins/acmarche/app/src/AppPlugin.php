<?php

namespace AcMarche\App;

use AcMarche\App\Traits\PluginTrait;
use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\HtmlString;

class AppPlugin implements Plugin
{
    use PluginTrait;

    public function getId(): string
    {
        return 'acapp';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        $panel
            ->when($panel->getId() == 'admin', function (Panel $panel) {
                $panel
                    ->discoverResources(
                        in: $this->getPluginBasePath('/Filament/Resources'),
                        for: 'AcMarche\\App\\Filament\\Resources'
                    )
                    ->discoverPages(
                        in: $this->getPluginBasePath('/Filament/Pages'),
                        for: 'AcMarche\\App\\Filament\\Pages'
                    )
                    ->discoverClusters(
                        in: $this->getPluginBasePath('/Filament/Clusters'),
                        for: 'AcMarche\\App\\Filament\\Clusters'
                    )
                    ->discoverClusters(
                        in: $this->getPluginBasePath('/Filament/Widgets'),
                        for: 'AcMarche\\App\\Filament\\Widgets'
                    )
                    ->navigationItems([
                        NavigationItem::make('Sécurité')
                            ->url('/security')
                            ->icon('tabler-users')
                            ->group('Gestion')
                            ->sort(3),
                        NavigationItem::make('News')
                            ->label(fn(): string => 'Quoi de neuf?')
                            ->icon('tabler-radio')
                            ->url('/news'),
                        NavigationItem::make('Documents')
                            ->label(fn(): string => 'Documents utiles')
                            ->icon('tabler-briefcase')
                            ->url('/document'),
                        NavigationItem::make('grh')
                            ->label(fn(): string => 'Resources humaines')
                            ->icon('tabler-school')
                            ->url('/grh'),
                    ]);
            });
    }

    public function boot(Panel $panel): void
    {
        FilamentView::registerRenderHook(
            name: 'panels::scripts.before',
            hook: fn() => new HtmlString(
                html: "
            <script>
                document.addEventListener('livewire:navigated', function() {
                    setTimeout(() => {
                        const activeSidebarItem = document.querySelector('nav .fi-sidebar-item-active');

                        const sidebarWrapper = document.querySelector('nav.fi-sidebar-nav');

                        sidebarWrapper.scrollTo(0, activeSidebarItem.offsetTop - 250);
                    }, 0);
                });
            </script>
        "
            )
        );
    }
}
