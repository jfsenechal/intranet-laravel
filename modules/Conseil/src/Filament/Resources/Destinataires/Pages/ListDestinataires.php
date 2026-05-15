<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Destinataires\Pages;

use AcMarche\Conseil\Filament\Resources\Destinataires\DestinataireResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListDestinataires extends ListRecords
{
    #[Override]
    protected static string $resource = DestinataireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau destinataire')
                ->icon(Heroicon::Plus),
        ];
    }
}
