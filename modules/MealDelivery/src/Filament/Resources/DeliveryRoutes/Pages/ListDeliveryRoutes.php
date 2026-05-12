<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\DeliveryRoutes\Pages;

use AcMarche\MealDelivery\Filament\Resources\DeliveryRoutes\DeliveryRouteResource;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListDeliveryRoutes extends ListRecords
{
    #[Override]
    protected static string $resource = DeliveryRouteResource::class;

    public function getTitle(): string
    {
        return 'Les tournées';
    }

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
