<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Filament\Resources\Archive\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class ArchiveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                TextInput::make('title')
                    ->label('Intitulé')
                    ->maxLength(255)
                    ->nullable(),
                Textarea::make('recipients')
                    ->label('Destinataires')
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('sent_at')
                    ->label('Date d\'envoi')
                    ->required()
                    ->suffixIcon('tabler-calendar-stats'),
                RichEditor::make('content')
                    ->label('Contenu')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
