<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Activites\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class ActivitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nom')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('groupes_count')
                    ->counts('groupes')
                    ->label('Groupes')
                    ->sortable(),
                TextColumn::make('inscriptions_count')
                    ->counts('inscriptions')
                    ->label('Inscriptions')
                    ->sortable(),
                IconColumn::make('archive')
                    ->label('Archivée')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Mise à jour')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nom')
            ->filters([
                TernaryFilter::make('archive')
                    ->label('Archive')
                    ->placeholder('Actives uniquement')
                    ->trueLabel('Archivées')
                    ->falseLabel('Actives')
                    ->default(false),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Voir')
                    ->icon(Heroicon::Eye),
                EditAction::make()
                    ->label('Modifier')
                    ->icon(Heroicon::PencilSquare),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Supprimer la selection')
                        ->icon(Heroicon::Trash),
                ]),
            ]);
    }
}
