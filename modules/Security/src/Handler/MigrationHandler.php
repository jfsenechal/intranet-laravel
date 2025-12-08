<?php

namespace AcMarche\Security\Handler;

use AcMarche\Document\Filament\Resources\DocumentResource;
use AcMarche\Mileage\Filament\Resources\TripResource;
use AcMarche\News\Filament\Resources\NewsResource;
use AcMarche\Publication\Filament\Resources\PublicationResource;
use AcMarche\Security\Filament\Resources\UserResource;
use AcMarche\Security\Models\Module;

final class MigrationHandler
{
    public static function urlModule(Module $module): string
    {
        $resource = self::findTheResource($module);
        if ($resource) {
            return $resource;
        }

        return '/admin';
    }

    public static function findTheResource(Module $module): ?string
    {
        return match ($module->id) {
            9 => DocumentResource::getUrl('index'),
            13 => TripResource::getUrl('index'),
            15 => NewsResource::getUrl('index'),
            17 => UserResource::getUrl('index'),
            44 => PublicationResource::getUrl('index'),
            default => null,
        };
    }
}
