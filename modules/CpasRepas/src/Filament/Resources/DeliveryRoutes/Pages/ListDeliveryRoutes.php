<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\DeliveryRoutes\Pages;

use AcMarche\CpasRepas\Filament\Resources\DeliveryRoutes\DeliveryRouteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListDeliveryRoutes extends ListRecords
{
    #[Override]
    protected static string $resource = DeliveryRouteResource::class;

    public function getTitle(): string
    {
        return 'Delivery Routes';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add delivery route')
                ->icon('tabler-plus'),
        ];
    }
}
