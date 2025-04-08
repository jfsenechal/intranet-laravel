<?php

namespace AcMarche\Support;

use AcMarche\Support\Database\Seeders\DatabaseSeeder;

class SupportServiceProvider extends PackageServiceProvider
{
    public static string $name = 'ac-support';
    public static string $viewNamespace = 'ac-support';

    public function configureCustomPackage(Package $package): void
    {
        $package->name(static::$name)
            ->isCore()
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations([
                '2024_11_05_105102_create_plugins_table',
                '2024_11_05_105112_create_plugin_dependencies_table',
                '0001_01_01_000000_create_support_table',
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
