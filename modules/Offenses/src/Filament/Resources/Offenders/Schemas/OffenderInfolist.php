<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class OffenderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Coordonnées')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('street')
                            ->label('Rue')
                            ->placeholder('—'),
                        TextEntry::make('postal_code')
                            ->label('Code postal')
                            ->placeholder('—'),
                        TextEntry::make('city')
                            ->label('Localité')
                            ->placeholder('—'),
                        TextEntry::make('birth_date')
                            ->label('Date de naissance')
                            ->date('d/m/Y')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
