<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Profiles\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class HistoriesRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'histories';

    #[Override]
    protected static ?string $title = 'Historique';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Attribut')
                    ->badge(),
                TextColumn::make('old_value')
                    ->label('Ancienne valeur')
                    ->formatStateUsing(fn ($state): string => is_array($state) ? implode(', ', array_map(
                        fn ($v) => is_scalar($v) ? (string) $v : json_encode($v),
                        $state,
                    )) : (string) $state)
                    ->limit(80)
                    ->wrap(),
                TextColumn::make('new_value')
                    ->label('Nouvelle valeur')
                    ->formatStateUsing(fn ($state): string => is_array($state) ? implode(', ', array_map(
                        fn ($v) => is_scalar($v) ? (string) $v : json_encode($v),
                        $state,
                    )) : (string) $state)
                    ->limit(80)
                    ->wrap(),
                TextColumn::make('username')
                    ->label('Par')
                    ->badge(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
