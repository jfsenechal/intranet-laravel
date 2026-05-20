<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Activities\Pages;

use AcMarche\SportsActivities\Filament\Resources\Activities\ActivityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListActivities extends ListRecords
{
    #[Override]
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle activité')
                ->icon(Heroicon::Plus),
        ];
    }
}
