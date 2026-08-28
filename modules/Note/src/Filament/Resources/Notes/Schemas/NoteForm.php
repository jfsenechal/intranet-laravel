<?php

declare(strict_types=1);

namespace AcMarche\Note\Filament\Resources\Notes\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class NoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                TextInput::make('name')
                    ->label('Titre')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Toggle::make('is_encrypted')
                    ->label('Chiffrer le contenu')
                    ->helperText('Le contenu est stocké chiffré en base de données. Il reste lisible ici, mais pas pour qui consulte la base directement.')
                    ->columnSpanFull(),
                RichEditor::make('content')
                    ->label('Contenu')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
