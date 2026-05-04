<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Event\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class EventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('title')
                    ->label('Intitulé'),
                TextEntry::make('event_type')
                    ->label('Type d\'événement'),
                TextEntry::make('organizer')
                    ->label('Organisateur'),
                TextEntry::make('description')
                    ->label('Objet')
                    ->columnSpanFull(),
                TextEntry::make('start_at')
                    ->label('Date de début')
                    ->dateTime('d/m/Y H:i')
                    ->icon('tabler-calendar-stats'),
                TextEntry::make('end_at')
                    ->label('Date de fin')
                    ->dateTime('d/m/Y H:i')
                    ->icon('tabler-calendar-stats'),
                TextEntry::make('reminder_at')
                    ->label('Date de rappel')
                    ->dateTime('d/m/Y H:i')
                    ->icon('tabler-bell'),
                TextEntry::make('location')
                    ->label('Lieu'),
                TextEntry::make('representative')
                    ->label('Représentant'),
                IconEntry::make('is_walk')
                    ->label('Marche')
                    ->boolean(),
                IconEntry::make('sent')
                    ->label('Envoyé')
                    ->boolean(),
            ]);
    }
}
