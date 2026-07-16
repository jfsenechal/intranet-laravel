<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages;

use AcMarche\CpasLibrary\Filament\Resources\Fiches\FicheResource;
use AcMarche\CpasLibrary\Models\Fiche;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Override;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
                ->action(function (Fiche $record): ?StreamedResponse {
                    $disk = Storage::disk('cpas-library');
                    $path = (string) $record->fileName;

                    if (! $disk->exists($path)) {
                        Notification::make()
                            ->title('Fichier introuvable')
                            ->body('Le fichier associé à cette fiche est introuvable.')
                            ->danger()
                            ->send();

                        return null;
                    }

                    return $disk->download($path, self::downloadFileName($record));
                }),
            EditAction::make()
                ->label('Modifier')
                ->color('warning')
                ->icon(Heroicon::PencilSquare),
            DeleteAction::make()
                ->label('Supprimer')
                ->icon(Heroicon::Trash),
        ];
    }

    /**
     * Files are stored under a generated name, which is meaningless to the person
     * downloading them. Offer the fiche name instead, keeping the stored extension.
     */
    private static function downloadFileName(Fiche $record): string
    {
        $name = Str::slug((string) $record->name) ?: 'fiche-'.$record->id;
        $extension = pathinfo((string) $record->fileName, PATHINFO_EXTENSION);

        return $extension === '' ? $name : $name.'.'.$extension;
    }
}
