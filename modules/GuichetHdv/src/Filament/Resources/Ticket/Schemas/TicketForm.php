<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Ticket\Schemas;

use AcMarche\GuichetHdv\Models\Reason;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make([
                    Grid::make(2)->schema([
                        TextInput::make('number')
                            ->label('Numéro')
                            ->required()
                            ->maxLength(255),
                        Select::make('reason')
                            ->label('Motif')
                            ->options(fn (): array => Reason::query()->orderBy('content')->pluck('content', 'content')->all())
                            ->searchable()
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('service')
                            ->label('Service')
                            ->required()
                            ->maxLength(255),
                        Select::make('office_id')
                            ->label('Guichet')
                            ->relationship('office', 'name')
                            ->searchable()
                            ->nullable(),
                    ]),
                ])->heading('Informations'),
                Section::make([
                    Grid::make(2)->schema([
                        DateTimePicker::make('assigned_date')
                            ->label('Date d\'assignation')
                            ->nullable(),
                        TextInput::make('assigned_by')
                            ->label('Assigné par')
                            ->maxLength(255)
                            ->nullable(),
                        Toggle::make('archive')
                            ->label('Archivé')
                            ->default(false),
                    ]),
                ])->heading('Assignation'),
            ]);
    }
}
