<?php

namespace AcMarche\Support;

use AcMarche\Support\Traits\PluginTrait;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\HtmlString;

class SupportPlugin implements Plugin
{
    use PluginTrait;

    public function getId(): string
    {
        return 'ac-support';
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
                        for: 'AcMarche\\Support\\Filament\\Resources'
                    )
                    ->discoverPages(
                        in: $this->getPluginBasePath('/Filament/Pages'),
                        for: 'AcMarche\\Support\\Filament\\Pages'
                    )
                    ->discoverClusters(
                        in: $this->getPluginBasePath('/Filament/Clusters'),
                        for: 'AcMarche\\Support\\Filament\\Clusters'
                    )
                    ->discoverClusters(
                        in: $this->getPluginBasePath('/Filament/Widgets'),
                        for: 'AcMarche\\Support\\Filament\\Widgets'
                    );
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
