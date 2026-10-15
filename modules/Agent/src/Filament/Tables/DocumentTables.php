<?php

namespace AcMarche\Agent\Filament\Tables;

use AcMarche\Agent\Filament\Pages\ShowDocument;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentTables
{
    public static function configure(Table $table, \closure $closure): Table
    {
        return $table
            ->records($closure)
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->weight(FontWeight::SemiBold)
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Modifié')
                    ->since()
                    ->sortable(),
            ])
            ->searchable()
            ->defaultSort('title')
            ->recordUrl(fn(array $record): string => ShowDocument::getUrl(['document' => $record['slug']]))
            ->emptyStateHeading('Aucune page')
            ->emptyStateDescription('La base de connaissances est vide.');
    }
}
