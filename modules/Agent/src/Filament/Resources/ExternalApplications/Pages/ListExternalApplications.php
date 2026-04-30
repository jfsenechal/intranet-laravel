<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\ExternalApplications\Pages;

use AcMarche\Agent\Filament\Resources\ExternalApplications\ExternalApplicationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListExternalApplications extends ListRecords
{
    #[Override]
    protected static string $resource = ExternalApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle application')
                ->icon('tabler-plus'),
        ];
    }
}
