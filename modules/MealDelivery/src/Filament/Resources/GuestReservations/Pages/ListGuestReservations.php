<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\GuestReservations\Pages;

use AcMarche\MealDelivery\Filament\Resources\GuestReservations\GuestReservationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListGuestReservations extends ListRecords
{
    #[Override]
    protected static string $resource = GuestReservationResource::class;

    public function getTitle(): string
    {
        return 'Réservations des invités';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter une réservation')
                ->icon('tabler-plus'),
        ];
    }
}
