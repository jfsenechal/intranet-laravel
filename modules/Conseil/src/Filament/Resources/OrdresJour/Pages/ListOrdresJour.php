<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\OrdresJour\Pages;

use AcMarche\Conseil\Filament\Resources\OrdresJour\OrdreJourResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListOrdresJour extends ListRecords
{
    #[Override]
    protected static string $resource = OrdreJourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvel ordre du jour')
                ->icon(Heroicon::Plus),
        ];
    }
}
