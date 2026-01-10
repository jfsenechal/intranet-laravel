<?php

namespace AcMarche\App;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class AppPlugin implements Plugin
{
    public static function make(): static
    {
        return app(self::class);
    }

    public function getId(): string
    {
        return 'app';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->discoverResources(
                in: __DIR__.'/Filament/Resources',
                for: 'AcMarche\\App\\Filament\\Resources',
            )
            ->discoverPages(
                in: __DIR__.'/Filament/Pages',
                for: 'AcMarche\\App\\Filament\\Pages',
            )
            ->discoverWidgets(
                in: __DIR__.'/Filament/Widgets',
                for: 'AcMarche\\App\\Filament\\Widgets',
            );
    }

    public function boot(Panel $panel): void
    {
        // Implement boot() method.
    }
}
