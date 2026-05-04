<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Archive\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class ArchiveInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('title')
                    ->label('Intitulé'),
                TextEntry::make('sent_at')
                    ->label('Date d\'envoi')
                    ->dateTime('d/m/Y H:i')
                    ->icon('tabler-calendar-stats'),
                TextEntry::make('recipients')
                    ->label('Destinataires')
                    ->columnSpanFull(),
                TextEntry::make('content')
                    ->label('Contenu')
                    ->html()
                    ->columnSpanFull()
                    ->prose(),
            ]);
    }
}
