<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Trips\Pages;

use AcMarche\Mileage\Filament\Resources\Declarations\DeclarationResource;
use AcMarche\Mileage\Filament\Resources\Trips\TripResource;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\Trip;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
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
            Action::make('viewDeclaration')
                ->label('Voir la déclaration')
                ->icon('tabler-file-invoice')
                ->color('gray')
                ->url(fn (Trip $record): ?string => $record->declaration instanceof Declaration
                    ? DeclarationResource::getUrl('view', ['record' => $record->declaration])
                    : null)
                ->visible(fn (Trip $record): bool => $record->declaration instanceof Declaration
                    && (auth()->user()?->can('view', $record->declaration) ?? false)),
            // Hidden by TripPolicy from the owner once the trip is declared; the form explains why.
            EditAction::make()
                ->icon('tabler-edit'),
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
