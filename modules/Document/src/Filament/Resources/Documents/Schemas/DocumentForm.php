<?php

declare(strict_types=1);

namespace AcMarche\Document\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Flex::make([
                    Section::make([
                        TextInput::make('name')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        RichEditor::make('content')
                            ->label('Description')
                            ->columnSpanFull(),
                        Hidden::make('file_name'),
                        Hidden::make('file_mime'),
                        Hidden::make('file_size'),
                        FileUpload::make('file_path')
                            ->label('Pièce jointe')
                            ->required()
                            ->disk('public')
                            ->directory(config('document.uploads.documents'))
                            ->previewable(false)
                            ->downloadable()
                            ->maxSize(10240)
                            ->afterStateUpdated(function ($state, Set $set): void {
                                if ($state instanceof TemporaryUploadedFile) {
                                    $set('file_name', $state->getFilename());
                                    $set('file_mime', $state->getMimeType());
                                    $set('file_size', $state->getSize());
                                }
                            }),
                    ]),
                    Section::make([
                        Select::make('category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->required(),
                    ])->grow(false),
                ])->from('md'),
            ]);
    }
}
