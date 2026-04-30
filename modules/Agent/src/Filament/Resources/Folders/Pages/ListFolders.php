<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Folders\Pages;

use AcMarche\Agent\Filament\Resources\Folders\FolderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListFolders extends ListRecords
{
    #[Override]
    protected static string $resource = FolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau dossier')
                ->icon('tabler-plus'),
        ];
    }
}
