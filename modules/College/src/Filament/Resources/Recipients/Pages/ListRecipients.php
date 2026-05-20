<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Recipients\Pages;

use AcMarche\College\Filament\Resources\Recipients\RecipientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListRecipients extends ListRecords
{
    #[Override]
    protected static string $resource = RecipientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau destinataire')
                ->icon(Heroicon::Plus),
        ];
    }
}
