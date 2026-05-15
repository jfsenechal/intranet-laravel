<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\OrdresJour\Pages;

use AcMarche\Conseil\Filament\Resources\OrdresJour\OrdreJourResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditOrdreJour extends EditRecord
{
    #[Override]
    protected static string $resource = OrdreJourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
