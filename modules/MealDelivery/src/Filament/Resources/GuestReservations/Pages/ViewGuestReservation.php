<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\GuestReservations\Pages;

use AcMarche\MealDelivery\Filament\Resources\GuestReservations\GuestReservationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewGuestReservation extends ViewRecord
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
            EditAction::make()
                ->icon(Heroicon::Pencil),
            DeleteAction::make()
                ->label('Supprimer la réservation')
                ->icon(Heroicon::Trash),
        ];
    }
}
