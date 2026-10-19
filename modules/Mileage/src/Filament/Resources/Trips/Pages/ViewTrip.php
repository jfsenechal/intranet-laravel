<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Trips\Pages;

use AcMarche\Mileage\Filament\Resources\Trips\TripResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;
use Override;

final class ViewTrip extends ViewRecord
{
    #[Override]
    protected static string $resource = TripResource::class;

    public function getTitle(): string
    {
        return 'Détails du déplacement '.$this->record->id;
    }

    protected function getHeaderActions(): array
    {
        return [
            // Hidden by TripPolicy once the trip is declared; the form explains why.
            EditAction::make()
                ->icon('tabler-edit'),
            DeleteAction::make()
                ->icon('tabler-trash'),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
