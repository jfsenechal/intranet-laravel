<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Groupes\Pages;

use AcMarche\Conseil\Filament\Resources\Groupes\GroupeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListGroupes extends ListRecords
{
    #[Override]
    protected static string $resource = GroupeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau groupe')
                ->icon(Heroicon::Plus),
        ];
    }
}
