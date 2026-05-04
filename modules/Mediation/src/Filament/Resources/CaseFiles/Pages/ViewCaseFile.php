<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\CaseFiles\Pages;

use AcMarche\Mediation\Filament\Resources\CaseFiles\CaseFileResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Override;

final class ViewCaseFile extends ViewRecord
{
    #[Override]
    protected static string $resource = CaseFileResource::class;

    public function getTitle(): string
    {
        return 'Dossier N°'.$this->record->number.' - '.$this->record->nature;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->icon('tabler-edit'),
            DeleteAction::make()->icon('tabler-trash'),
        ];
    }
}
