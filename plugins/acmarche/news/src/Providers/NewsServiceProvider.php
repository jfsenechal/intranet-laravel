<?php

namespace AcMarche\News\Providers;

use AcMarche\Support\Package;
use AcMarche\Support\Providers\PackageServiceProvider;

class NewsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'ac-news';

    public function configureCustomPackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->isCore()//do migration
            ->hasViews()
            ->hasMigrations(['0001_01_01_000000_create_news_table'])
            ->runsMigrations()
            ->hasConfigFile([
                'news'
            ]);
    }
}
