<?php

namespace AcMarche\Courrier\Filament\Resources\Recipients\Schemas;

use AcMarche\Courrier\Models\Recipient;
use Filament\Forms;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;

final class RecipientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                Flex::make([
                    Forms\Components\Toggle::make('is_active')
                        ->label('Actif')
                        ->helperText('Le destinataire sera visible lors de l\ajout d\'un courrier')
                        ->default(true),
                    Forms\Components\Toggle::make('receives_attachments')
                        ->label('Reçoit les pièces jointes')
                        ->helperText('Lors de la notification, les courriers seront attachés au mail')
                        ->default(false),
                ]),
                Forms\Components\Select::make('supervisor_id')
                    ->label('Superviseur')
                    ->helperText('Utilisé par le CPAS')
                    ->relationship('supervisor', 'last_name')
                    ->getOptionLabelFromRecordUsing(fn (Recipient $record) => "{$record->first_name} {$record->last_name}")
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }
}
