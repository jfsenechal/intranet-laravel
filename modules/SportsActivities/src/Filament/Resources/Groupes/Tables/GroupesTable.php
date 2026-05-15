<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Groupes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class GroupesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('activite.nom')
                    ->label('Activité')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('jour')->label('Jour')->sortable()->searchable(),
                TextColumn::make('heure')->label('Heure')->sortable(),
                TextColumn::make('lieux')->label('Lieu')->sortable()->searchable(),
                TextColumn::make('age')->label('Âge'),
                TextColumn::make('prix')->label('Prix')->money('EUR')->sortable(),
                TextColumn::make('inscriptions_count')
                    ->counts('inscriptions')
                    ->label('Inscriptions'),
            ])
            ->filters([
                SelectFilter::make('activite_id')
                    ->label('Activité')
                    ->relationship('activite', 'nom'),
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
