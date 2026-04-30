<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informations')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Intitule')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true),
                        TextInput::make('abbreviation')
                            ->label('Abreviation')
                            ->maxLength(255),
                        Select::make('direction_id')
                            ->label('Direction')
                            ->relationship('direction', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('employer_id')
                            ->label('Employeur')
                            ->relationship('employer', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
                Section::make('Coordonnees')
                    ->columns(2)
                    ->schema([
                        TextInput::make('address')
                            ->label('Adresse')
                            ->maxLength(100)
                            ->columnSpanFull(),
                        TextInput::make('postal_code')
                            ->label('Code postal')
                            ->numeric(),
                        TextInput::make('city')
                            ->label('Ville')
                            ->maxLength(100),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telephone')
                            ->tel()
                            ->maxLength(150),
                        TextInput::make('gsm')
                            ->label('GSM')
                            ->tel()
                            ->maxLength(150),
                    ]),
                Section::make('Remarques')
                    ->schema([
                        RichEditor::make('notes')
                            ->label('Remarques')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
