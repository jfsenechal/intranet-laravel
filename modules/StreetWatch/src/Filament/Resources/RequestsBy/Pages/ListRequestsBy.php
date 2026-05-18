<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Filament\Resources\RequestsBy\Pages;

use AcMarche\StreetWatch\Filament\Resources\RequestsBy\RequestByResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListRequestsBy extends ListRecords
{
    #[Override]
    protected static string $resource = RequestByResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau demandeur')
                ->icon(Heroicon::Plus),
        ];
    }
}
