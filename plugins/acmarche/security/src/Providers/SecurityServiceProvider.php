<?php

namespace AcMarche\Security\Providers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SecurityServiceProvider extends PackageServiceProvider
{
    public static string $name = 'acsecurity';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews()
            ->hasMigrations([
                '0001_01_01_000000_create_security_table',
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

    }
}
