<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Inscriptions\Pages;

use AcMarche\SportsActivities\Filament\Resources\Inscriptions\InscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListInscriptions extends ListRecords
{
    #[Override]
    protected static string $resource = InscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle inscription')
                ->icon(Heroicon::Plus),
        ];
    }
}
