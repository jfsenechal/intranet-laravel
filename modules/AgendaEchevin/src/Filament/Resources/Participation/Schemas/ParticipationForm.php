<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Filament\Resources\Participation\Schemas;

use AcMarche\AgendaEchevin\Models\Event;
use AcMarche\AgendaEchevin\Models\Recipient;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

final class ParticipationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Select::make('event_id')
                    ->label('Événement')
                    ->options(fn () => Event::query()->pluck('title', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('recipient_id')
                    ->label('Destinataire')
                    ->options(fn () => Recipient::query()->get()->mapWithKeys(
                        fn (Recipient $r) => [$r->id => $r->last_name.' '.$r->first_name]
                    ))
                    ->searchable()
                    ->required(),
                Select::make('response')
                    ->label('Réponse')
                    ->options([
                        '1' => 'Oui',
                        '0' => 'Non',
                    ])
                    ->placeholder('Sans réponse')
                    ->nullable(),
            ]);
    }
}
