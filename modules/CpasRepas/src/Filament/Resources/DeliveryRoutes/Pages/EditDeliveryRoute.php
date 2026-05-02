<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\DeliveryRoutes\Pages;

use AcMarche\CpasRepas\Filament\Resources\DeliveryRoutes\DeliveryRouteResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditDeliveryRoute extends EditRecord
{
    #[Override]
    protected static string $resource = DeliveryRouteResource::class;

    public function getTitle(): string
    {
        return 'Edit delivery route';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
