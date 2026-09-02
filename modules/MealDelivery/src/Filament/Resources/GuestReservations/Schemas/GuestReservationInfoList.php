<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\GuestReservations\Schemas;

use AcMarche\MealDelivery\Models\GuestReservation;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class GuestReservationInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('client.last_name')
                                    ->label('Client')
                                    ->state(fn (GuestReservation $record): string => mb_trim(
                                        ($record->client?->last_name ?? '').' '.($record->client?->first_name ?? ''),
                                    ))
                                    ->placeholder('—'),

                                TextEntry::make('date')
                                    ->label('Date du repas')
                                    ->date('d/m/Y'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('menu1_count')
                                    ->label('Menu 1'),

                                TextEntry::make('menu2_count')
                                    ->label('Menu 2'),

                                TextEntry::make('total')
                                    ->label('Total invités')
                                    ->state(fn (GuestReservation $record): int => $record->totalCount()),
                            ]),

                        TextEntry::make('notes')
                            ->label('Remarques')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
