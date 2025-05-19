<?php

namespace AcMarche\Document\Form;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DocumentForm
{
    public static function createForm(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Split::make([
                    Forms\Components\Section::make([
                        Forms\Components\TextInput::make('name')
                            ->label('Titre')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenu')
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('file_name'),
                        Forms\Components\Hidden::make('file_mime'),
                        Forms\Components\Hidden::make('file_size'),
                        FileUpload::make('medias')
                            ->label('Pièce jointe')
                            ->required()
                            ->disk('public')
                            ->directory('uploads/document')
                            ->previewable(false)
                            ->downloadable()
                            ->maxSize(10240)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state instanceof TemporaryUploadedFile) {
                                    $set('file_name', $state->getFilename());
                                    $set('file_mime', $state->getMimeType());
                                    $set('file_size', $state->getSize());
                                }
                            }),
                    ]),
                    Forms\Components\Section::make([
                        Forms\Components\Select::make('category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->required(),
                    ])->grow(false),
                ])->from('md'),
            ]);
    }
}
