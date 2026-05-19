<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Agendas\Pages;

use AcMarche\Conseil\Filament\Resources\Agendas\AgendaResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditAgenda extends EditRecord
{
    #[Override]
    protected static string $resource = AgendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
