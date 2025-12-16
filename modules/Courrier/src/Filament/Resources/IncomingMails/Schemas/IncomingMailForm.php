<?php

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Schemas;

use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use Filament\Forms;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class IncomingMailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Flex::make([
                    Section::make('Informations du courrier')
                        ->schema([
                            Forms\Components\TextInput::make('reference_number')
                                ->label('Numéro de référence')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\DatePicker::make('mail_date')
                                ->label('Date du courrier')
                                ->required()
                                ->default(now())
                                ->native(false),
                            Forms\Components\TextInput::make('sender')
                                ->label('Expéditeur')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\RichEditor::make('description')
                                ->label('Description')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->columnSpan(2),
                    Section::make('Options')
                        ->schema([
                            Forms\Components\Toggle::make('is_notified')
                                ->label('Notifié')
                                ->default(false),
                            Forms\Components\Toggle::make('is_registered')
                                ->label('Recommandé')
                                ->default(false),
                            Forms\Components\Toggle::make('has_acknowledgment')
                                ->label('Accusé de réception')
                                ->default(false),
                        ])
                        ->grow(false),
                ])->from('md'),

                Section::make('Affectation')
                    ->schema([
                        Forms\Components\Select::make('services')
                            ->label('Services')
                            ->relationship('services', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Service $record) => $record->initials ? "{$record->name} ({$record->initials})" : $record->name)
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->pivotData(['is_primary' => false]),
                        Forms\Components\Select::make('recipients')
                            ->label('Destinataires')
                            ->relationship('recipients', 'last_name')
                            ->getOptionLabelFromRecordUsing(fn (Recipient $record) => "{$record->first_name} {$record->last_name}")
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->pivotData(['is_primary' => false]),
                    ])
                    ->columns(2),
            ]);
    }
}
