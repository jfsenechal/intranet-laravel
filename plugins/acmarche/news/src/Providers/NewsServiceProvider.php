<?php

namespace AcMarche\News\Providers;

use AcMarche\Support\Package;
use AcMarche\Support\Providers\PackageServiceProvider;
use Spatie\LaravelPackageTools\PackageServiceProvider as SpatiePackageServiceProvider;

class NewsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'acnews';

    protected function mergeConfigFrom22($path, $key)
    {
        $config = $this->app->make('config');
        $data = require $path;

        $config->set('database.connections.marianews', $data['maria-news']);
    }

    public function configureCustomPackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->isCore()//do migration
            ->hasViews()
            ->hasConfigFile([
               // 'database',
            ])
            ->hasMigrations(['0001_01_01_000000_create_news_table'])
            ->runsMigrations();
    }

    public function configurePackage(\Spatie\LaravelPackageTools\Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews()
            ->hasConfigFile([
               // 'database',
            ])
            ->hasMigrations(['0001_01_01_000000_create_news_table'])
            ->runsMigrations();

    }
}
