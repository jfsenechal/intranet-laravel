<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\DeliveryRoutes\Pages;

use AcMarche\MealDelivery\Filament\Resources\DeliveryRoutes\DeliveryRouteResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateDeliveryRoute extends CreateRecord
{
    #[Override]
    protected static string $resource = DeliveryRouteResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Add delivery route';
    }
}
