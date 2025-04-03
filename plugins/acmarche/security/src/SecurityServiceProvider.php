<?php

namespace AcMarche\Security;

use AcMarche\Support\Package;
use AcMarche\Support\PackageServiceProvider;
use AcMarche\Support\Traits\PluginTrait;

class SecurityServiceProvider extends PackageServiceProvider
{
    use PluginTrait;

    public static string $name = 'ac-security';

    public function configureCustomPackage(Package $package): void
    {
        $package->name(static::$name)
            ->isCore()
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations([
                '0001_01_01_000000_create_security_table'
            ])
            ->runsMigrations()
            ->hasCommands([

            ]);
    }

    public function packageBooted(): void
    {

    }

    public function packageRegistered(): void
    {
        //
    }
}
