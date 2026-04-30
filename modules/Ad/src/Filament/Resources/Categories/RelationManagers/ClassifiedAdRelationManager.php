<?php

declare(strict_types=1);

namespace AcMarche\Ad\Filament\Resources\Categories\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Override;

final class ClassifiedAdRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'ad';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Intitulé')
                    ->limit(80)
                    ->searchable(),
                IconColumn::make('archive')
                    ->label('Archivé')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('archive')
                    ->label('Archivé')
                    ->boolean()
                    ->default(false)
                    ->trueLabel('Archivés seulement')
                    ->falseLabel('Non archivés seulement')
                    ->native(false),
            ]);
    }
}
