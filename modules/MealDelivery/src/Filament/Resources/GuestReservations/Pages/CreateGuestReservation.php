<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\GuestReservations\Pages;

use AcMarche\MealDelivery\Filament\Resources\GuestReservations\GuestReservationResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateGuestReservation extends CreateRecord
{
    #[Override]
    protected static string $resource = GuestReservationResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter une réservation';
    }
}
