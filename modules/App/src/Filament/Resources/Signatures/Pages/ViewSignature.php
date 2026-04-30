<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Signatures\Pages;

use AcMarche\App\Filament\Resources\Signatures\SignatureResource;
use AcMarche\App\Models\Signature;
use AcMarche\App\Services\SignatureHtmlGenerator;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\Response;
use Override;

final class ViewSignature extends ViewRecord
{
    #[Override]
    protected static string $resource = SignatureResource::class;

    public function getTitle(): string
    {
        return 'Ma signature';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->icon(Heroicon::Pencil),
            Action::make('download')
                ->label('Télécharger HTML')
                ->icon(Heroicon::ArrowDownTray)
                ->color('success')
                ->action(function (Signature $record): Response {
                    $html = SignatureHtmlGenerator::generate($record);

                    return response($html, 200, [
                        'Content-Type' => 'text/html; charset=UTF-8',
                        'Content-Disposition' => 'attachment; filename="signature-'.$record->id.'.html"',
                    ]);
                }),
            Action::make('copy')
                ->label('Copier le code HTML')
                ->icon(Heroicon::ClipboardDocument)
                ->color('primary')
                ->modalHeading('Code HTML de la signature')
                ->modalDescription('Sélectionnez et copiez le code ci-dessous.')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer')
                ->modalContent(fn (Signature $record) => view('app::emails.signature-copy', [
                    'html' => SignatureHtmlGenerator::generate($record),
                ])),
            DeleteAction::make()->icon(Heroicon::Trash),
        ];
    }
}
