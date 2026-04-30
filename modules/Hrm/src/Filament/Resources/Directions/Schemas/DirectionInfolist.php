<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Directions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class DirectionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informations')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Intitulé'),
                        TextEntry::make('abbreviation')
                            ->label('Abréviation'),
                        TextEntry::make('director')
                            ->label('Directeur'),
                        TextEntry::make('employer.name')
                            ->label('Employeur'),
                    ]),
            ]);
    }
}
