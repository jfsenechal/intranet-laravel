<?php

namespace AcMarche\Security\Handler;

use AcMarche\Document\Filament\Resources\DocumentResource;
use AcMarche\Mileage\Filament\Resources\TripResource;
use AcMarche\News\Filament\Resources\NewsResource;
use AcMarche\Publication\Filament\Resources\PublicationResource;
use AcMarche\Security\Filament\Resources\UserResource;
use AcMarche\Security\Models\Module;
use Filament\Resources\Resource;

final class MigrationHandler
{
    public static function urlModule(Module $module): string
    {
        $resource = self::correspondance($module);
        if ($resource) {
            return $resource::getUrl('index');
        }

        return '/admin';
    }

    public static function correspondance(Module $module): ?Resource
    {
        return match ($module->id) {
            9 => DocumentResource::class,
            13 => TripResource::class,
            15 => NewsResource::class,
            17 => UserResource::class,
            44 => PublicationResource::class,
            default => '/admin'
        };
    }
}
