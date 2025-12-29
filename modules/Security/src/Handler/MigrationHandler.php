<?php

namespace AcMarche\Security\Handler;

use AcMarche\Courrier\Filament\Resources\IncomingMails\IncomingMailResource;
use AcMarche\Document\Filament\Resources\Documents\DocumentResource;
use AcMarche\Hrm\Filament\Resources\Employees\EmployeeResource;
use AcMarche\Mileage\Filament\Resources\Trips\TripResource;
use AcMarche\News\Filament\Resources\News\NewsResource;
use AcMarche\Publication\Filament\Resources\Publications\PublicationResource;
use AcMarche\Security\Filament\Resources\Users\UserResource;
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
            6 => EmployeeResource::getUrl('index', panel: 'hrm-panel'),
            9 => DocumentResource::getUrl('index', panel: 'document-panel'),
            13 => TripResource::getUrl('index', panel: 'mileage-panel'),
            15 => NewsResource::getUrl('index', panel: 'news'),
            16 => IncomingMailResource::getUrl('index', panel: 'courrier-panel'),
            17 => UserResource::getUrl('index', panel: 'security-panel'),
            44 => PublicationResource::getUrl('index', panel: 'publication-panel'),
            default => null,
        };
    }
}
