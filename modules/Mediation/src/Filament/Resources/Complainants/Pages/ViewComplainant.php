<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\Complainants\Pages;

use AcMarche\Mediation\Filament\Resources\CaseFiles\CaseFileResource;
use AcMarche\Mediation\Filament\Resources\Complainants\ComplainantResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewComplainant extends ViewRecord
{
    #[Override]
    protected static string $resource = ComplainantResource::class;

    public function getTitle(): string
    {
        return $this->record->last_name.' '.$this->record->first_name;
    }

    protected function getHeaderActions(): array
    {
        $id = ['complainant_id' => $this->record->id];

        return [
            Action::make('addCaseFile')
                ->label('Ajouter un dossier')
                ->icon(Heroicon::Plus)
                ->color('success')
                ->url(CaseFileResource::getUrl('create', $id)),
            EditAction::make()->icon(Heroicon::Pencil),
            DeleteAction::make()->icon(Heroicon::Trash),
        ];
    }
}
