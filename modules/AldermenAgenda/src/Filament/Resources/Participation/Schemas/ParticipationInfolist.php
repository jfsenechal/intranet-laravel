<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Participation\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class ParticipationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('event.title')
                    ->label('Événement'),
                TextEntry::make('recipient.last_name')
                    ->label('Destinataire')
                    ->formatStateUsing(
                        fn ($state, $record) => $record->recipient->last_name.' '.$record->recipient->first_name
                    ),
                IconEntry::make('response')
                    ->label('Réponse')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}
