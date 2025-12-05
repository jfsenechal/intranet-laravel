<?php

namespace AcMarche\Mileage\Filament\Resources\PersonalInformation\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class PersonalInformationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('username')
            ->columns([
                TextColumn::make('street')
                    ->label('Rue'),
                TextColumn::make('city')
                    ->label('Localité'),
                TextColumn::make('iban')
                    ->label('Compte IBAN'),
                TextColumn::make('car_license_plate1')
                    ->label('Plaque d\'immatriculation 1'),
                TextColumn::make('car_license_plate2')
                    ->label('Plaque d\'immatriculation 2')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('omnium')
                    ->label('Retenue omnium')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('college_trip_date')
                    ->label('Date de la décision du Collège')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
