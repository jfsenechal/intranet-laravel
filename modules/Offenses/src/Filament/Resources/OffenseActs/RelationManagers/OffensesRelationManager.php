<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\OffenseActs\RelationManagers;

use AcMarche\Offenses\Filament\Resources\Offenses\OffenseResource;
use AcMarche\Offenses\Models\Offense;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Override;

final class OffensesRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'offenses';

    #[Override]
    protected static ?string $title = 'Incivilités';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('decision_date', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('offender.last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('offender.first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('decision_date')
                    ->label('Décision')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('—'),

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
                TernaryFilter::make('mediation')
                    ->label('Médiation'),
            ])
            // The relation manager has no schema of its own, so both actions
            // lead to the offense resource pages rather than to a modal.
            ->recordUrl(fn (Offense $record): string => OffenseResource::getUrl('view', ['record' => $record]))
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Offense $record): string => OffenseResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->url(fn (Offense $record): string => OffenseResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
