<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\GuestReservations\Pages;

use AcMarche\MealDelivery\Filament\Resources\GuestReservations\GuestReservationResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditGuestReservation extends EditRecord
{
    #[Override]
    protected static string $resource = GuestReservationResource::class;

    public function getTitle(): string
    {
        return 'Réservation du '.($this->record->date?->format('d/m/Y') ?? '');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
