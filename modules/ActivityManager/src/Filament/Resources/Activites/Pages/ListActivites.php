<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Activites\Pages;

use AcMarche\ActivityManager\Filament\Resources\Activites\ActiviteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListActivites extends ListRecords
{
    #[Override]
    protected static string $resource = ActiviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle activité')
                ->icon(Heroicon::Plus),
        ];
    }
}
