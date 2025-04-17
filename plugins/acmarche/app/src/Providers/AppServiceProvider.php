<?php

namespace AcMarche\App\Providers;

use AcMarche\App\Database\Seeders\DatabaseSeeder;
use AcMarche\App\Package;

class AppServiceProvider extends PackageServiceProvider
{
    public static string $name = 'acapp';

    public function configureCustomPackage(Package $package): void
    {
        $package->name(static::$name)
            ->isCore()
            ->hasViews()
            ->hasMigrations([
                '2024_11_05_105102_create_plugins_table',
                '0001_01_01_000000_create_app_table',
            ])
            ->runsMigrations()
            ->hasSeeder(
                DatabaseSeeder::class,
            )
            ->hasCommands([

            ]);
    }

    public function packageBooted(): void
    {
        //   Livewire::component('accept-invitation', AcceptInvitation::class);

        //     Gate::policy(Role::class, RolePolicy::class);
    }

    public function packageRegistered(): void
    {
        //
    }
}
