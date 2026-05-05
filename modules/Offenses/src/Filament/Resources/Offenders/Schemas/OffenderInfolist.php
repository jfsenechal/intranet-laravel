<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenses\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class OffenderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Contrevenant')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('offender.birth_date')
                            ->label('Date de naissance')
                            ->date('d/m/Y')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('offender.street')
                            ->label('Rue')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('offender.postal_code')
                            ->label('Code postal')
                            ->placeholder('—'),
                        TextEntry::make('offender.city')
                            ->label('Localité')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
