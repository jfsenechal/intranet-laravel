<?php

namespace AcMarche\News\Providers;

//use AcMarche\App\Package;
//use AcMarche\App\Providers\PackageServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class NewsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'acnews';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations([
                '0001_01_01_000000_create_news_table',
            ]);
    }

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
}
