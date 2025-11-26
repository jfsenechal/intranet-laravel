<?php

namespace AcMarche\Mileage\Filament\Resources\DeclarationResource\Tables;

use AcMarche\Mileage\Filament\Resources\DeclarationResource;
use AcMarche\Mileage\Models\Declaration;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class DeclarationTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Declaration $record) => DeclarationResource::getUrl('view', ['record' => $record->id])),

                Tables\Columns\TextColumn::make('prenom')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('plaque1')
                    ->label('Plaque')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type_deplacement')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('omnium')
                    ->label('Omnium')
                    ->boolean(),

                Tables\Columns\TextColumn::make('date_college')
                    ->label('Date collège')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
