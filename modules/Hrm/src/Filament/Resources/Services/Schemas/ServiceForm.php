<?php

namespace AcMarche\Hrm\Filament\Resources\Services\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

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
                        Forms\Components\TextInput::make('title')
                            ->label('Intitule')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(80),
                        Forms\Components\TextInput::make('abbreviation')
                            ->label('Abreviation')
                            ->maxLength(255),
                        Forms\Components\Select::make('direction_id')
                            ->label('Direction')
                            ->relationship('direction', 'title')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('employer_id')
                            ->label('Employeur')
                            ->relationship('employer', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
                Section::make('Coordonnees')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->label('Adresse')
                            ->maxLength(100)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('postal_code')
                            ->label('Code postal')
                            ->numeric(),
                        Forms\Components\TextInput::make('city')
                            ->label('Ville')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telephone')
                            ->tel()
                            ->maxLength(150),
                        Forms\Components\TextInput::make('gsm')
                            ->label('GSM')
                            ->tel()
                            ->maxLength(150),
                    ]),
                Section::make('Remarques')
                    ->schema([
                        Forms\Components\RichEditor::make('notes')
                            ->label('Remarques')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
