<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Services\Pages;

use AcMarche\Hrm\Filament\Resources\Services\ServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListServices extends ListRecords
{
    #[Override]
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un service')
                ->icon('tabler-plus'),
        ];
    }
}
