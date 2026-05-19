<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Groups\Pages;

use AcMarche\Conseil\Filament\Resources\Groups\GroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListGroups extends ListRecords
{
    #[Override]
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau groupe')
                ->icon(Heroicon::Plus),
        ];
    }
}
