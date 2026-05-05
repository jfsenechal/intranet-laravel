<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Trips\Tables;

use AcMarche\Mileage\Filament\CreateDeclarationAction;
use AcMarche\Mileage\Filament\Resources\Trips\TripResource;
use AcMarche\Mileage\Models\Trip;
use AcMarche\Mileage\Repository\TripRepository;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class TripTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('departure_date', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => TripRepository::getByUser($query))
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('departure_date')
                    ->label('Date')
                    ->dateTime('d/m/Y')
                    ->sortable()                    ,
                TextColumn::make('departure_location')
                    ->label('Départ')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('arrival_location')
                    ->label('Arrivée')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('distance')
                    ->label('Distance')
                    ->sortable()
                    ->suffix(' km'),
                TextColumn::make('type_movement')
                    ->label('Type')
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('declared')
                    ->label('Déclaré')
                    ->state(fn (Trip $record): bool => $record->isDeclared())
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('declared')
                    ->label('Déclaré')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('declaration_id'),
                        false: fn ($query) => $query->whereNull('declaration_id'),
                    )
                    ->default(false),
                SelectFilter::make('type_movement')
                    ->label('Type')
                    ->options([
                        'externe' => 'Externe',
                        'service' => 'Service',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->recordAction(ViewAction::class)
            ->toolbarActions([
                BulkActionGroup::make([
                    CreateDeclarationAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
