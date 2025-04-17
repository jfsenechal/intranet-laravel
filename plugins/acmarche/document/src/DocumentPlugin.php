<?php

namespace AcMarche\Document;

use Filament\Contracts\Plugin;
use Filament\Panel;

class DocumentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'acdocument';
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
