<?php

namespace AcMarche\Courrier\Filament\Resources\Recipients\Schemas;

use AcMarche\Courrier\Models\Recipient;
use Filament\Forms;
use Filament\Schemas\Schema;

final class RecipientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('last_name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('first_name')
                    ->label('Prénom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('username')
                    ->label('Nom d\'utilisateur')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('supervisor_id')
                    ->label('Superviseur')
                    ->relationship('supervisor', 'last_name')
                    ->getOptionLabelFromRecordUsing(fn (Recipient $record) => "{$record->first_name} {$record->last_name}")
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Actif')
                    ->default(true),
                Forms\Components\Toggle::make('receives_attachments')
                    ->label('Reçoit les pièces jointes')
                    ->default(false),
            ]);
    }
}
