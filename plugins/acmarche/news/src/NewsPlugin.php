<?php

namespace AcMarche\News;

use Filament\Contracts\Plugin;
use Filament\Panel;

class NewsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'ac-news';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {

    }

    public function boot(Panel $panel): void
    {

    }
}
