<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Schemas;

use AcMarche\Courrier\Filament\Components\DepartmentField;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Sender;
use AcMarche\Courrier\Repository\RecipientRepository;
use AcMarche\Courrier\Repository\ServiceRepository;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

final class IncomingMailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(self::getComponents());
    }

    /**
     * @param  array<string, mixed>|null  $imapPreview  IMAP preview context: ['url', 'contentType', 'filename']
     */
    public static function getComponents(?array $imapPreview = null): array
    {
        $components = [];

        if ($imapPreview !== null) {
            // IMAP flow: show preview from IMAP server
            $contentType = $imapPreview['contentType'] ?? '';
            $isPreviewable = str_starts_with($contentType, 'image/')
                || $contentType === 'application/pdf';

            if ($isPreviewable) {
                $components[] = Section::make('Aperçu')
                    ->schema([
                        View::make('courrier::components.attachment-preview')
                            ->viewData([
                                'url' => $imapPreview['url'],
                                'contentType' => $contentType,
                                'filename' => $imapPreview['filename'] ?? '',
                            ]),
                    ])
                    ->collapsible();
            }
        } else {
            // Manual flow: file upload with client-side preview
            // Show existing attachment preview when editing
            $components[] = Section::make('Pièce jointe')
                ->schema([
                    View::make('courrier::components.attachment-preview')
                        ->viewData(fn (?IncomingMail $record): array => self::getExistingAttachmentPreviewData($record))
                        ->visible(fn (?IncomingMail $record): bool => $record?->attachments->isNotEmpty() ?? false),
                    FileUpload::make('attachment_file')
                        ->label(fn (?IncomingMail $record): string => $record instanceof IncomingMail ? 'Remplacer le fichier' : 'Fichier')
                        ->required(fn (?IncomingMail $record): bool => ! $record instanceof IncomingMail)
                        ->acceptedFileTypes(config('courrier.allowed_mime_types'))
                        ->maxSize(config('courrier.max_file_size'))
                        ->storeFiles(false)
                        ->previewable(false),
                    View::make('courrier::components.upload-preview'),
                ]);
        }

        // Add the form
        $components[] = Flex::make([
            Section::make('Informations du courrier')
                ->schema([
                    TextInput::make('reference_number')
                        ->label('Numéro')
                        ->required()
                        ->maxLength(255),
                    DatePicker::make('mail_date')
                        ->label('Date du courrier')
                        ->required()
                        ->default(now())
                        ->native(false),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('sender')
                                ->label('Expéditeur')
                                ->required()
                                ->maxLength(255)
                                ->datalist(Sender::query()->pluck('name')->toArray())
                                ->columnSpan(1),
                            Checkbox::make('save_sender')
                                ->label('Enregistrer l\'expéditeur')
                                ->inline()
                                ->columnSpan(1),
                        ]),
                    Textarea::make('description')
                        ->label('Description')
                        ->rows(4)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpan(2),
            Section::make('Options')
                ->schema([
                    Toggle::make('is_registered')
                        ->label('Recommandé ?')
                        ->default(false),
                    Toggle::make('has_acknowledgment')
                        ->label('Accusé de réception ?')
                        ->default(false),
                    Toggle::make('is_notified')
                        ->label('Notifié')
                        ->default(false),
                ])
                ->grow(false),
        ])->from('md');

        // Add department field
        $departmentFields = DepartmentField::make();
        if ($departmentFields !== []) {
            $components[] = Section::make('Département')
                ->schema($departmentFields);
        }

        // Add services and recipients
        $components[] = Section::make('Affectation')
            ->schema([
                Select::make('primary_services')
                    ->label('Services principaux')
                    ->options(ServiceRepository::findAllActiveOrdered())
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Select::make('secondary_services')
                    ->label('Services secondaires')
                    ->options(ServiceRepository::findAllActiveOrdered())
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Select::make('primary_recipients')
                    ->label('Destinataires principaux')
                    ->options(RecipientRepository::getForOptions())
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Select::make('secondary_recipients')
                    ->label('Destinataires secondaires')
                    ->options(RecipientRepository::getForOptions())
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ])
            ->columns(2);

        return $components;
    }

    /**
     * @return array{url: string, contentType: string, filename: string}
     */
    private static function getExistingAttachmentPreviewData(?IncomingMail $record): array
    {
        $attachment = $record?->attachments->first();

        if (! $attachment) {
            return ['url' => '', 'contentType' => '', 'filename' => ''];
        }

        return [
            'url' => route('courrier.attachments.preview-stored', $attachment),
            'contentType' => $attachment->mime ?? '',
            'filename' => $attachment->file_name,
        ];
    }
}
