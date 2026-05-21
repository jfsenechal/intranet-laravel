<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Agendas\Pages;

use AcMarche\Conseil\Filament\Resources\Agendas\AgendaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListAgendas extends ListRecords
{
    #[Override]
    protected static string $resource = AgendaResource::class;

    protected static ?string $title = 'Ordres du jour';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvel ordre du jour')
                ->icon(Heroicon::Plus),
        ];
    }
}
