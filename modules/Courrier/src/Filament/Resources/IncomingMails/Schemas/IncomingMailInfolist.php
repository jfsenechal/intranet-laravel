<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Schemas;

use AcMarche\Courrier\Models\Attachment;
use AcMarche\Courrier\Models\IncomingMail;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

final class IncomingMailInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations du courrier')
                    ->schema([
                        TextEntry::make('reference_number')
                            ->label('Numéro de référence')
                            ->badge()
                            ->color('gray'),
                        TextEntry::make('mail_date')
                            ->label('Date du courrier')
                            ->date('d/m/Y'),
                        TextEntry::make('sender')
                            ->label('Expéditeur'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->html()
                            ->columnSpanFull()
                            ->prose()
                            ->hidden(fn ($state): bool => blank($state)),
                        TextEntry::make('follow_up_note')
                            ->label('Note de suivi')
                            ->columnSpanFull()
                            ->prose()
                            ->hidden(fn ($state): bool => blank($state)),
                    ])
                    ->columns(2),

                Section::make('Métas données')
                    ->schema([
                        // Shown only while it is true: a courrier is a draft
                        // briefly and validated for the rest of its life, so an
                        // entry reading "Brouillon : non" on every other mail
                        // would be noise. The view page is reachable by URL even
                        // though the draft listing links to the edit form, so
                        // this is what says the metadata has not been read yet.
                        TextEntry::make('is_draft')
                            ->hiddenLabel()
                            ->badge()
                            ->color('warning')
                            ->icon('tabler-sparkles')
                            ->formatStateUsing(fn (): string => 'Brouillon IA — métadonnées non vérifiées')
                            ->hidden(fn ($state): bool => ! $state)
                            ->columnSpanFull(),
                        TextEntry::make('department')
                            ->label('Département')
                            ->badge()
                            ->color('info')
                            ->hidden(fn ($state): bool => blank($state)),
                        Flex::make([
                            IconEntry::make('is_notified')
                                ->label('Notifié')
                                ->boolean(),
                            IconEntry::make('is_registered')
                                ->label('Recommandé')
                                ->boolean(),
                            IconEntry::make('has_acknowledgment')
                                ->label('Accusé de réception')
                                ->boolean(),
                        ]),
                        Flex::make([
                            TextEntry::make('user_add')
                                ->label('Créé par'),
                            TextEntry::make('created_at')
                                ->label('Date de création')
                                ->dateTime('d/m/Y H:i'),
                            TextEntry::make('updated_at')
                                ->label('Dernière modification')
                                ->dateTime('d/m/Y H:i'),
                        ]),
                    ]),

                Section::make('Affectation')
                    ->schema([
                        TextEntry::make('services.name')
                            ->label('Services')
                            ->badge()
                            ->separator(',')
                            ->hidden(fn ($state): bool => blank($state)),
                        TextEntry::make('recipients.full_name')
                            ->label('Destinataires')
                            ->badge()
                            ->color('gray')
                            ->separator(',')
                            ->hidden(fn ($state): bool => blank($state)),
                    ])
                    ->columns(2),

                Section::make('Contenu (OCR)')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('content')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->prose(),
                    ])
                    ->visible(fn ($record): bool => filled($record->content)
                        && self::canPreviewAttachment($record))
                    ->collapsed(),

                Section::make('Pièce jointe')
                    ->icon('tabler-paperclip')
                    ->schema([
                        View::make('courrier::components.attachment-preview')
                            ->viewData(fn ($record): array => self::attachmentPreviewData($record)),
                    ])
                    ->visible(fn ($record): bool => self::canPreviewAttachment($record)
                        && self::hasStoredFile($record)),
            ]);
    }

    /**
     * The mail's attachment may only be shown to users allowed to download it;
     * users who merely index a department can see the mail but not its file.
     */
    private static function canPreviewAttachment(IncomingMail $incomingMail): bool
    {
        $attachment = $incomingMail->attachments->first();

        return $attachment instanceof Attachment
            && (Auth::user()?->can('download', $attachment) ?? false);
    }

    /**
     * Legacy mail sometimes references a file that is no longer on disk; the
     * preview route would 404 and leave an empty frame, so hide it instead.
     */
    private static function hasStoredFile(IncomingMail $incomingMail): bool
    {
        $attachment = $incomingMail->attachments->first();

        return $attachment instanceof Attachment
            && $attachment->path !== null
            && Storage::disk(config('courrier.storage.disk'))->exists($attachment->path);
    }

    /**
     * @return array{url: string, contentType: string, filename: string}
     */
    private static function attachmentPreviewData(IncomingMail $incomingMail): array
    {
        $attachment = $incomingMail->attachments->first();

        if (! $attachment instanceof Attachment) {
            return ['url' => '', 'contentType' => '', 'filename' => ''];
        }

        return [
            'url' => route('courrier.attachments.preview-stored', $attachment),
            'contentType' => $attachment->mime ?? '',
            'filename' => $attachment->file_name,
        ];
    }
}
