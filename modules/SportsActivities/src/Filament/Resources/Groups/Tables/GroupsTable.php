<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Groups\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class GroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('activity.name')
                    ->label('Activité')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('day')->label('Jour')->sortable()->searchable(),
                TextColumn::make('time')->label('Heure')->sortable(),
                TextColumn::make('location')->label('Lieu')->sortable()->searchable(),
                TextColumn::make('age')->label('Âge'),
                TextColumn::make('price')->label('Prix')->money('EUR')->sortable(),
                TextColumn::make('registrations_count')
                    ->counts('registrations')
                    ->label('Inscriptions'),
            ])
            ->filters([
                SelectFilter::make('activity_id')
                    ->label('Activité')
                    ->relationship('activity', 'name'),
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
