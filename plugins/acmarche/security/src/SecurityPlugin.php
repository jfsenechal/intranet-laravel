<?php

namespace AcMarche\Security;

use AcMarche\Support\Traits\PluginTrait;
use Filament\Contracts\Plugin;
use Filament\Panel;

class SecurityPlugin implements Plugin
{
    use PluginTrait;

    public function getId(): string
    {
        return 'ac-security';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        $panel
            ->when($panel->getId() == 'admin', function (Panel $panel) {
                $panel->passwordReset()
                    ->discoverResources(
                        in: $this->getPluginBasePath('/Filament/Resources'),
                        for: 'AcMarche\\Security\\Filament\\Resources'
                    )
                    ->discoverPages(
                        in: $this->getPluginBasePath('/Filament/Pages'),
                        for: 'AcMarche\\Security\\Filament\\Pages'
                    )
                    ->discoverClusters(
                        in: $this->getPluginBasePath('/Filament/Clusters'),
                        for: 'AcMarche\\Security\\Filament\\Clusters'
                    )
                    ->discoverClusters(
                        in: $this->getPluginBasePath('/Filament/Widgets'),
                        for: 'AcMarche\\Security\\Filament\\Widgets'
                    );
            });
    }

    public function boot(Panel $panel): void
    {

    }
}
