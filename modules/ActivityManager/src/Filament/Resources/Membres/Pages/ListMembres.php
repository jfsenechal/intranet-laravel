<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Membres\Pages;

use AcMarche\ActivityManager\Filament\Resources\Membres\MembreResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListMembres extends ListRecords
{
    #[Override]
    protected static string $resource = MembreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau membre')
                ->icon(Heroicon::Plus),
        ];
    }
}
