<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Fiches\Schemas;

use AcMarche\CpasLibrary\Enums\FicheTypeEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

final class FicheForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identification')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
                                'slug',
                                Str::slug($state ?? ''),
                            )),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->maxLength(255),
                        Select::make('category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('type')
                            ->label('Type')
                            ->options(FicheTypeEnum::class)
                            ->default(FicheTypeEnum::DEFAULT->value),
                        Select::make('tags')
                            ->label('Tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ]),

                Section::make('Contenu')
                    ->schema([
                        RichEditor::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        FileUpload::make('file_upload')
                            ->label('Fichier')
                            ->disk('cpas-library')
                            ->directory('fiches')
                            ->visibility('private')
                            ->maxSize(51200)
                            ->dehydrated(false)
                            ->afterStateUpdated(function ($state, Set $set): void {
                                if (! $state instanceof UploadedFile) {
                                    return;
                                }
                                $set('fileName', $state->getClientOriginalName());
                                $set('fileSize', $state->getSize());
                                $set('mimeType', $state->getMimeType());
                            })
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            TextInput::make('fileName')
                                ->label('Nom du fichier')
                                ->maxLength(190),
                            TextInput::make('mimeType')
                                ->label('Type MIME')
                                ->maxLength(255),
                            TextInput::make('fileSize')
                                ->label('Taille (octets)')
                                ->numeric(),
                            TextInput::make('source')
                                ->label('Source')
                                ->url()
                                ->maxLength(255),
                            TextInput::make('type_document')
                                ->label('Type de document')
                                ->maxLength(255),
                        ]),
                    ]),

                Section::make('Dates')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('date_promulgation')
                            ->label('Date de promulgation'),
                        DatePicker::make('date_publication')
                            ->label('Date de publication'),
                        DatePicker::make('date_rappel')
                            ->label('Date de rappel'),
                        DatePicker::make('date_begin')
                            ->label('Date de début')
                            ->beforeOrEqual('date_end'),
                        DatePicker::make('date_end')
                            ->label('Date de fin')
                            ->afterOrEqual('date_begin'),
                    ]),
            ]);
    }
}
