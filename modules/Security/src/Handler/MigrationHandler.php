<?php

namespace AcMarche\Security\Handler;

use AcMarche\Document\Filament\Resources\DocumentResource;
use AcMarche\Mileage\Filament\Resources\Trip\TripResource;
use AcMarche\News\Filament\Resources\NewsResource;
use AcMarche\Publication\Filament\Resources\PublicationResource;
use AcMarche\Security\Filament\Resources\UserResource\UserResource;
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
            9 => DocumentResource::getUrl('index', panel: 'document-panel'),
            13 => TripResource::getUrl('index', panel: 'mileage-panel'),
            15 => NewsResource::getUrl('index', panel: 'news'),
            17 => UserResource::getUrl('index', panel: 'security-panel'),
            44 => PublicationResource::getUrl('index', panel: 'publication-panel'),
            default => null,
        };
    }
}
