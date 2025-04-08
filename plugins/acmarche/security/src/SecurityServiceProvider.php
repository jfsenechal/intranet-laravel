<?php

namespace AcMarche\Security;

use AcMarche\Support\Package;
use AcMarche\Support\PackageServiceProvider;
use AcMarche\Support\Traits\PluginTrait;
use Filament\Facades\Filament;
use Filament\Panel;

class SecurityServiceProvider extends PackageServiceProvider
{
    use PluginTrait;

    public static string $name = 'ac-security';
    public static string $viewNamespace = 'ac-security';

    public function configureCustomPackage(Package $package): void
    {
        $package->name(static::$name)
            ->isCore()
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations([
                '0001_01_01_000000_create_security_table',
            ])
            ->runsMigrations()
        //    ->hasSeeder()
            ->hasCommands([

            ]);
    }

    public function packageBooted(): void
    {
        Filament::registerPanel(
            Panel::make()
                ->id('plugin-panel')
                ->path('plugin')
                ->brandName('Plugin Panel')
                ->plugins([
                    // Add your plugin services here
                ]),
        );
    }

    public function packageRegistered(): void
    {
        Filament::registerPanel(
            Panel::make()
                ->id('plugin-panel')
                ->path('plugin')
                ->brandName('Plugin Panel')
                ->plugins([
                    // Add your plugin services here
                ]),
        );
        //
    }
}
