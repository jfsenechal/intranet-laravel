<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages;

use AcMarche\CpasLibrary\Filament\Resources\Fiches\FicheResource;
use AcMarche\CpasLibrary\Models\Fiche;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Override;

final class ViewFiche extends ViewRecord
{
    #[Override]
    protected static string $resource = FicheResource::class;

    public function getTitle(): string
    {
        return (string) $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Télécharger')
                ->icon(Heroicon::ArrowDownTray)
                ->visible(fn (Fiche $record): bool => $record->fileName !== null)
                ->action(fn (Fiche $record) => Storage::disk('cpas-library')->download(
                    'fiches/'.$record->fileName,
                    $record->fileName,
                )),
            EditAction::make()
                ->label('Modifier')
                ->color('warning')
                ->icon(Heroicon::PencilSquare),
            DeleteAction::make()
                ->label('Supprimer')
                ->icon(Heroicon::Trash),
        ];
    }
}
