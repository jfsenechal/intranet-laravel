<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Valorizations\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ValorizationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Valorisation')
                    ->columns(2)
                    ->schema([
                        TextInput::make('employer_name')
                            ->label('Employeur')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('duration')
                            ->label('Durée')
                            ->helperText('Exemple: 9 mois')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('regime')
                            ->label('Régime horaire')
                            ->maxLength(150),
                    ]),
                Section::make('Contenu')
                    ->schema([
                        RichEditor::make('content')
                            ->label('Contenu')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ]),
                Section::make('Attestation')
                    ->schema([
                        FileUpload::make('file_name')
                            ->label('Fichier attestation')
                            ->disk('public')
                            ->visibility('public')
                            ->directory(config('hrm.uploads.valorizations'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
