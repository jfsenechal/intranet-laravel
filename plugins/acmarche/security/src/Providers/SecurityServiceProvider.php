<?php

namespace AcMarche\Security\Providers;

use AcMarche\App\Package;
use AcMarche\App\Providers\PackageServiceProvider;

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
