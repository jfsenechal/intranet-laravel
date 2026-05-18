<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Filament\Resources\Incidents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class IncidentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('createdAt', 'desc')
            ->columns([
                TextColumn::make('occurred_date')
                    ->label("Date de l'incident")
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('place')
                    ->label('Lieu')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('object')
                    ->label('Objet')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(60),
                TextColumn::make('typeIncident.name')
                    ->label('Type')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('requestBy.name')
                    ->label('Demandé par')
                    ->toggleable(),
                TextColumn::make('user_add')
                    ->label('Auteur')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('createdAt')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('typeIncident_id')
                    ->label('Type')
                    ->relationship('typeIncident', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('requestBy_id')
                    ->label('Demandé par')
                    ->relationship('requestBy', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('user_add')
                    ->schema([
                        TextInput::make('user_add')->label('Auteur'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['user_add'] ?? null,
                        fn (Builder $q, string $value) => $q->where('user_add', $value),
                    )),
                TernaryFilter::make('has_response')
                    ->label('Avec suite')
                    ->placeholder('Tous')
                    ->trueLabel('Avec suite')
                    ->falseLabel('Sans suite')
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('response'),
                        false: fn (Builder $q) => $q->whereNull('response'),
                        blank: fn (Builder $q) => $q,
                    ),
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
                        ->label('Supprimer la sélection')
                        ->icon(Heroicon::Trash),
                ]),
            ]);
    }
}
