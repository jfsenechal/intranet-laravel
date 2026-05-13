<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Filament\Resources\LineTypes\Pages;

use AcMarche\Telecommunication\Filament\Resources\LineTypes\LineTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListLineTypes extends ListRecords
{
    #[Override]
    protected static string $resource = LineTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau type de ligne')
                ->icon(Heroicon::Plus),
        ];
    }
}
