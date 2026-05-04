<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\CaseFiles\Pages;

use AcMarche\Mediation\Filament\Resources\CaseFiles\CaseFileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListCaseFiles extends ListRecords
{
    #[Override]
    protected static string $resource = CaseFileResource::class;

    public function getTitle(): string
    {
        return 'Dossiers de médiation';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau dossier')
                ->icon('tabler-plus'),
        ];
    }
}
