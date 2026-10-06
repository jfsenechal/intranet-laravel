<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Categories\Schemas;

use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Description')
                    ->columnSpan(2)
                    ->schema([
                        TextEntry::make('description')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->placeholder('—'),
                    ]),

                Section::make('Informations')
                    ->columnSpan(1)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('departments')
                            ->label('Départements')
                            ->badge()
                            ->separator(',')
                            ->placeholder('—'),
                        IconEntry::make('public')
                            ->label('Public')
                            ->boolean(),
                        ColorEntry::make('color')
                            ->label('Couleur')
                            ->placeholder('—'),
                        IconEntry::make('icon')
                            ->label('Icône')
                            ->icon(fn (?string $state): ?string => $state),
                    ]),
            ]);
    }
}
