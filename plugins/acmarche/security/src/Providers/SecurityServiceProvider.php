<?php

namespace AcMarche\Security\Providers;

use AcMarche\Support\Package;
use AcMarche\Support\Providers\PackageServiceProvider;

class SecurityServiceProvider extends PackageServiceProvider
{
    public static string $name = 'acsecurity';

    public function configureCustomPackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->isCore()
            ->hasViews()
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

    }

    public function packageRegistered(): void
    {

    }
}
