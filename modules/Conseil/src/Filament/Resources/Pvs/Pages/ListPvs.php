<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Pvs\Pages;

use AcMarche\Conseil\Filament\Resources\Pvs\PvResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListPvs extends ListRecords
{
    #[Override]
    protected static string $resource = PvResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau procès-verbal')
                ->icon(Heroicon::Plus),
        ];
    }
}
