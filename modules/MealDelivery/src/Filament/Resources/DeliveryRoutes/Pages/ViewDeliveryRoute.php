<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Clients\Pages;

use AcMarche\MealDelivery\Filament\Resources\DeliveryRoutes\DeliveryRouteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Override;

final class ViewDeliveryRoute extends ViewRecord
{
    #[Override]
    protected static string $resource = DeliveryRouteResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon('tabler-edit'),
            DeleteAction::make()
                ->label('Supprimer la tournée')
                ->icon('tabler-trash'),
        ];
    }
}
