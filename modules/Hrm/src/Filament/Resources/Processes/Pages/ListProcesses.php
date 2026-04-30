<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Processes\Pages;

use AcMarche\Hrm\Filament\Resources\Processes\ProcessResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListProcesses extends ListRecords
{
    #[Override]
    protected static string $resource = ProcessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau processus')
                ->icon(Heroicon::Plus),
        ];
    }
}
