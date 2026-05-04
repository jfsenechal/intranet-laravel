<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\OffenseActs\Tables;

use AcMarche\Offenses\Filament\Resources\OffenseActs\OffenseActResource;
use AcMarche\Offenses\Models\OffenseAct;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class OffenseActTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->url(fn (OffenseAct $record): string => OffenseActResource::getUrl('view', ['record' => $record->id])),

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
