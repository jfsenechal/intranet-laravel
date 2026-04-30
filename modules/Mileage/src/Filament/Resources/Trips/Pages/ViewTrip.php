<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Trips\Pages;

use AcMarche\Mileage\Filament\Resources\Trips\TripResource;
use AcMarche\Mileage\Models\Trip;
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
            EditAction::make()
                ->icon('tabler-edit')
                ->disabled(fn (Trip $trip): bool => $trip->isDeclared())
                ->tooltip(
                    fn (Trip $trip): ?string => $trip->isDeclared() ? 'Ce déplacement est déjà lié à une déclaration' : null
                ),
            DeleteAction::make()
                ->icon('tabler-trash'),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
