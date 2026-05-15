<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Ticket\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class TicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make([
                Grid::make(2)->schema([
                    TextEntry::make('number')
                        ->label('Numéro'),
                    TextEntry::make('reason')
                        ->label('Motif'),
                    TextEntry::make('service')
                        ->label('Service'),
                    TextEntry::make('office.name')
                        ->label('Guichet')
                        ->placeholder('—'),
                    TextEntry::make('user_add')
                        ->label('Créé par'),
                    TextEntry::make('createdAt')
                        ->label('Créé le')
                        ->dateTime('d/m/Y H:i'),
                ]),
            ])->heading('Informations'),
            Section::make([
                Grid::make(2)->schema([
                    TextEntry::make('assigned_by')
                        ->label('Assigné par')
                        ->placeholder('—'),
                    TextEntry::make('assigned_date')
                        ->label('Date d\'assignation')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('—'),
                    IconEntry::make('archive')
                        ->label('Archivé')
                        ->boolean(),
                ]),
            ])->heading('Assignation'),
        ]);
    }
}
