<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Filament\Resources\Event\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Flex::make([
                    Section::make([
                        TextInput::make('title')
                            ->label('Intitulé')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('event_type')
                            ->label('Type d\'événement')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('organizer')
                            ->label('Organisateur')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Objet')
                            ->required()
                            ->columnSpanFull(),
                        FileUpload::make('file1_name')
                            ->label('Pièce jointe 1')
                            ->disk('public')
                            ->directory(config('agenda_echevin.uploads.files'))
                            ->maxSize(7168)
                            ->nullable(),
                        FileUpload::make('file2_name')
                            ->label('Pièce jointe 2')
                            ->disk('public')
                            ->directory(config('agenda_echevin.uploads.files'))
                            ->maxSize(7168)
                            ->nullable(),
                    ]),
                    Section::make([
                        DateTimePicker::make('start_at')
                            ->label('Date de début')
                            ->required()
                            ->suffixIcon('tabler-calendar-stats'),
                        DateTimePicker::make('end_at')
                            ->label('Date de fin')
                            ->required()
                            ->suffixIcon('tabler-calendar-stats'),
                        DateTimePicker::make('reminder_at')
                            ->label('Date de rappel')
                            ->nullable()
                            ->suffixIcon('tabler-bell'),
                        TextInput::make('location')
                            ->label('Lieu')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('representative')
                            ->label('Représentant')
                            ->maxLength(255)
                            ->nullable(),
                        Toggle::make('is_walk')
                            ->label('Marche'),
                        Toggle::make('sent')
                            ->label('Envoyé'),
                    ])->grow(false),
                ])->from('md'),
            ]);
    }
}
