<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Sportifs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class SportifsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nom')->label('Nom')->sortable()->searchable(),
                TextColumn::make('prenom')->label('Prénom')->sortable()->searchable(),
                TextColumn::make('localite')->label('Localité')->sortable()->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('gsm')->label('GSM')->toggleable(),
                TextColumn::make('inscriptions_count')
                    ->counts('inscriptions')
                    ->label('Inscriptions')
                    ->sortable(),
            ])
            ->defaultSort('nom')
            ->filters([
                //
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
