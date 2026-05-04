<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenses\Tables;

use AcMarche\Offenses\Filament\Resources\Offenses\OffenseResource;
use AcMarche\Offenses\Models\Offense;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class OffenseTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('decision_date', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('decision_date')
                    ->label('Décision')
                    ->date('d/m/Y')
                    ->sortable()
                    ->url(fn (Offense $record): string => OffenseResource::getUrl('view', ['record' => $record->id])),

                TextColumn::make('offender.last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('offender.first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('offenseAct.name')
                    ->label('Acte')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fine_amount')
                    ->label('Amende')
                    ->money('EUR')
                    ->sortable()
                    ->placeholder('—'),

                IconColumn::make('mediation')
                    ->label('Médiation')
                    ->boolean(),

                TextColumn::make('prosecutor_opinion')
                    ->label('Avis procureur')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('offense_act_id')
                    ->label('Acte')
                    ->relationship('offenseAct', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('mediation')
                    ->label('Médiation'),

                Filter::make('with_fine')
                    ->label('Avec amende')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('fine_amount')),
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
