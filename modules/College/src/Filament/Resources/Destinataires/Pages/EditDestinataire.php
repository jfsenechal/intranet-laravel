<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Destinataires\Pages;

use AcMarche\College\Filament\Resources\Destinataires\DestinataireResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditDestinataire extends EditRecord
{
    #[Override]
    protected static string $resource = DestinataireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
