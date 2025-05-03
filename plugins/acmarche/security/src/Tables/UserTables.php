<?php

namespace AcMarche\Security\Tables;

use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables;

class UserTables
{
    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('last_name')
            ->columns([
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Prénom')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->icon('tabler-phone'),
                Tables\Columns\TextColumn::make('extension')
                    ->label('Extension')
                    ->icon('tabler-device-landline-phone'),
                Tables\Columns\TextColumn::make('roles.name'),
                Tables\Columns\TextColumn::make('username')
                    ->label('Nom d\'utilisateur')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->relationship('roles', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

}
