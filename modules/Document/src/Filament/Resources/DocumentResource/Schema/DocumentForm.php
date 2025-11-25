<?php

namespace AcMarche\Document\Filament\Resources\DocumentResource\Schema;

use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Flex::make([
                    Section::make([
                        Forms\Components\TextInput::make('name')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255)
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
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state instanceof TemporaryUploadedFile) {
                                    $set('file_name', $state->getFilename());
                                    $set('file_mime', $state->getMimeType());
                                    $set('file_size', $state->getSize());
                                }
                            }),
                    ]),
                    Section::make([
                        Forms\Components\Select::make('category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->required(),
                    ])->grow(false),
                ])->from('md'),
            ]);
    }

    public static function configure22(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('File')
                            ->required()
                            ->disk('public')
                            ->directory('documents')
                            ->preserveFilenames()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('category')
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Published')
                            ->default(false),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Publish Date'),
                    ]),
            ]);
    }
}
