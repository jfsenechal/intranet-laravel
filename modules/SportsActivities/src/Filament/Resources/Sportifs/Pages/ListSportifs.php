<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Sportifs\Pages;

use AcMarche\SportsActivities\Filament\Resources\Sportifs\SportifResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListSportifs extends ListRecords
{
    #[Override]
    protected static string $resource = SportifResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau sportif')
                ->icon(Heroicon::Plus),
        ];
    }
}
