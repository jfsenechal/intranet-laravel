<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Signatures\Pages;

use AcMarche\MailingList\Filament\Resources\Signatures\SignatureResource;
use AcMarche\MailingList\Models\Signature;
use AcMarche\MailingList\Services\SignatureHtmlService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Js;
use Override;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
                ->action(function (Signature $record): StreamedResponse {
                    $html = SignatureHtmlService::generate($record);

                    return response()->streamDownload(
                        function () use ($html): void {
                            echo $html;
                        },
                        'signature-'.$record->id.'.html',
                        ['Content-Type' => 'text/html; charset=UTF-8'],
                    );
                }),
            Action::make('copy')
                ->label('Copier le code HTML')
                ->icon(Heroicon::ClipboardDocument)
                ->color('primary')
                ->alpineClickHandler(function (Signature $record): string {
                    $html = Js::from(SignatureHtmlService::generate($record));
                    $successTitle = Js::from('Signature copiée dans le presse-papiers');
                    $failureTitle = Js::from('Impossible de copier la signature');

                    return <<<JS
                        window.navigator.clipboard.writeText({$html})
                            .then(() => new FilamentNotification().title({$successTitle}).success().send())
                            .catch(() => new FilamentNotification().title({$failureTitle}).danger().send())
                        JS;
                }),
            DeleteAction::make()->icon(Heroicon::Trash),
        ];
    }
}
