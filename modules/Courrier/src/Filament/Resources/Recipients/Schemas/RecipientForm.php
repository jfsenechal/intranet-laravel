<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Recipients\Schemas;

use AcMarche\Courrier\Models\Recipient;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
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
                    Toggle::make('receives_attachments')
                        ->label('Reçoit les pièces jointes')
                        ->helperText('Lors de la notification, les courriers seront attachés au mail')
                        ->default(false),
                ]),
                Select::make('supervisor_id')
                    ->label('Superviseur')
                    ->helperText('Le superviseur sera automatiquement mis en copie. Utilisé par le CPAS')
                    ->relationship('supervisor', 'last_name')
                    ->getOptionLabelFromRecordUsing(fn (Recipient $record): string => "{$record->first_name} {$record->last_name}")
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }
}
