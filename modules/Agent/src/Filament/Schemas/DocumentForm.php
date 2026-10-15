<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Schemas;

use AcMarche\Agent\Documentation\ImageStore;
use AcMarche\Agent\Documentation\SecretScanner;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Notifications\Notification;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class DocumentForm
{
    /**
     * Images are saved by ImageStore on the private disk. Because a custom save
     * callback is set, Filament skips its own "make the file public" step.
     */
    public static function contentField(): MarkdownEditor
    {
        return MarkdownEditor::make('content')
            ->label('Contenu')
            ->required()
            ->maxLength(500000)
            ->columnSpanFull()
            ->helperText('Ne jamais y écrire de mot de passe ni de clé : référencez le coffre-fort.')
            ->fileAttachmentsDisk('knowledge-base')
            ->fileAttachmentsDirectory(ImageStore::DIRECTORY)
            ->fileAttachmentsAcceptedFileTypes(['image/png', 'image/jpeg', 'image/gif', 'image/webp'])
            ->fileAttachmentsMaxSize(5120)
            ->saveUploadedFileAttachmentUsing(fn (TemporaryUploadedFile $file): string => app(ImageStore::class)->store($file))
            ->getFileAttachmentUrlUsing(fn (string $file): string => app(ImageStore::class)->url($file));
    }

    public static function warnAboutSecrets(string $content): void
    {
        $findings = app(SecretScanner::class)->findings($content);

        if ($findings === []) {
            return;
        }

        $lines = collect($findings)->pluck('line')->unique()->implode(', ');

        Notification::make()
            ->title('Possible secret détecté')
            ->body("Lignes : {$lines} — déplacez ces informations dans le coffre-fort.")
            ->warning()
            ->persistent()
            ->send();
    }
}
