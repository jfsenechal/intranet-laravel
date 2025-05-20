<?php

namespace AcMarche\App\Providers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AppServiceProvider extends PackageServiceProvider
{
    public static string $name = 'acapp';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews()
            ->hasMigrations([
                '2024_11_05_105102_create_plugins_table',
                '0001_01_01_000000_create_app_table',
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
