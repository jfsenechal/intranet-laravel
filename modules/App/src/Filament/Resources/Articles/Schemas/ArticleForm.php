<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Articles\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Article')
                    ->columns(1)
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('excerpt')
                            ->label('Extrait')
                            ->helperText('Court résumé affiché dans la liste des articles.')
                            ->rows(3)
                            ->maxLength(500),
                        RichEditor::make('body')
                            ->label('Contenu')
                            ->required(),
                    ]),
            ]);
    }
}
