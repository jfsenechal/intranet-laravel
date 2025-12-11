<?php

namespace AcMarche\Courrier\Filament\Resources\IncomingMailResource\Schema;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class IncomingMailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Flex::make([
                    Section::make([
                        Forms\Components\TextInput::make('reference')
                            ->label('Référence')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('received_date')
                            ->label('Date de réception')
                            ->required()
                            ->default(now())
                            ->native(false),
                        Forms\Components\TextInput::make('sender_name')
                            ->label('Expéditeur')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('sender_address')
                            ->label('Adresse de l\'expéditeur')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('subject')
                            ->label('Objet')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('attachment_name'),
                        Forms\Components\Hidden::make('attachment_mime'),
                        Forms\Components\Hidden::make('attachment_size'),
                        FileUpload::make('attachment_path')
                            ->label('Pièce jointe')
                            ->disk('public')
                            ->directory('uploads/courrier')
                            ->previewable(false)
                            ->downloadable()
                            ->maxSize(10240)
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state instanceof TemporaryUploadedFile) {
                                    $set('attachment_name', $state->getFilename());
                                    $set('attachment_mime', $state->getMimeType());
                                    $set('attachment_size', $state->getSize());
                                }
                            }),
                    ])->columnSpan(2),
                    Section::make([
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->required()
                            ->default('pending')
                            ->options([
                                'pending' => 'En attente',
                                'processed' => 'Traité',
                                'archived' => 'Archivé',
                            ]),
                        Forms\Components\TextInput::make('assigned_to')
                            ->label('Assigné à')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('processed_date')
                            ->label('Date de traitement')
                            ->native(false),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(4),
                    ])->grow(false),
                ])->from('md'),
            ]);
    }
}
