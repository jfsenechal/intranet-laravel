<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Rates\Pages;

use AcMarche\Mileage\Filament\Resources\Rates\RateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListRates extends ListRecords
{
    #[Override]
    protected static string $resource = RateResource::class;

    public function getTitle(): string
    {
        return 'Liste des tarifs';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un tarif')
                ->icon('tabler-plus'),
        ];
    }
}
