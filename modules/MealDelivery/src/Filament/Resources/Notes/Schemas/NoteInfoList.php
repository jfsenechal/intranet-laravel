<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Notes\Schemas;

use AcMarche\MealDelivery\Filament\Resources\Clients\ClientResource;
use AcMarche\MealDelivery\Models\Note;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class NoteInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('client')
                                    ->label('Client')
                                    ->state(fn (Note $record): string => (string) $record->client)
                                    ->url(fn (Note $record): ?string => $record->client_id === null
                                        ? null
                                        : ClientResource::getUrl('view', ['record' => $record->client_id]))
                                    ->placeholder('—'),

                                TextEntry::make('note_date')
                                    ->label('Ajoutée le')
                                    ->date('d/m/Y')
                                    ->placeholder('—'),

                                TextEntry::make('user_add')
                                    ->label('Ajoutée par')
                                    ->placeholder('—'),
                            ]),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),

                        IconEntry::make('is_done')
                            ->label('Traité')
                            ->boolean(),
                    ]),
            ]);
    }
}
