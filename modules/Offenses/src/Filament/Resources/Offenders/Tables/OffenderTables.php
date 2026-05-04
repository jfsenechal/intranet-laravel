<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenders\Tables;

use AcMarche\Offenses\Filament\Resources\Offenders\OffenderResource;
use AcMarche\Offenses\Models\Offender;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class OffenderTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('last_name')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Offender $record): string => OffenderResource::getUrl('view', ['record' => $record->id])),

                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('birth_date')
                    ->label('Naissance')
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('city')
                    ->label('Localité')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('postal_code')
                    ->label('CP')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('offenses_count')
                    ->label('Sanctions')
                    ->counts('offenses')
                    ->sortable(),
            ])
            ->filters([])
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
