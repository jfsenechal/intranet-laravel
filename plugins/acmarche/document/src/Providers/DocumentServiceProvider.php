<?php

namespace AcMarche\Document\Providers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DocumentServiceProvider extends PackageServiceProvider
{
    public static string $name = 'acdocument';

    public function configureCustomPackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->isCore()//do migration
            ->hasViews()
            ->hasMigrations(['0001_01_01_000000_create_document_table'])
            ->runsMigrations();
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews()
            ->hasConfigFile([
               // 'database',
            ])
            ->hasMigrations(['0001_01_01_000000_create_document_table'])
            ->runsMigrations();

    }
}
