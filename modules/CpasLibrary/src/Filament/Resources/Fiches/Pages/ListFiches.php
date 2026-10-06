<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages;

use AcMarche\CpasLibrary\Filament\Resources\Fiches\FicheResource;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListFiches extends ListRecords
{
    #[Override]
    protected static string $resource = FicheResource::class;

    protected function getHeaderActions(): array
    {
        return [
            FicheResource::createActionGroup(),
        ];
    }
}
