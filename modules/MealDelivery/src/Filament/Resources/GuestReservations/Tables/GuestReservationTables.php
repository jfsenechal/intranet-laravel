<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\GuestReservations\Tables;

use AcMarche\MealDelivery\Models\GuestReservation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

final class GuestReservationTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('resident_name')
                    ->label('Résident')
                    ->state(fn (GuestReservation $record): string => $record->resident?->fullName() ?? '')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereHas(
                            'resident',
                            fn (Builder $resident): Builder => $resident
                                ->where('last_name', 'like', '%'.$search.'%')
                                ->orWhere('first_name', 'like', '%'.$search.'%'),
                        ))
                    ->sortable(['resident_id']),

                TextColumn::make('resident.room')
                    ->label('Chambre')
                    ->placeholder('—')
                    ->toggleable(),

                ...self::countColumns(),

                TextColumn::make('notes')
                    ->label('Remarques')
                    ->placeholder('—')
                    ->wrap()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('resident_id')
                    ->label('Résident')
                    ->relationship('resident', 'last_name')
                    ->searchable()
                    ->preload(),

                Filter::make('upcoming')
                    ->label('À venir')
                    ->query(fn (Builder $query): Builder => $query->whereDate('date', '>=', today())),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->recordAction(ViewAction::class)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Same table nested under a resident, where the resident column is noise.
     */
    public static function inline(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                ...self::countColumns(),

                TextColumn::make('notes')
                    ->label('Remarques')
                    ->placeholder('—')
                    ->wrap(),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->recordAction(ViewAction::class)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * The menu breakdown and the guest total, summed across the whole table so
     * the day's expected head count is visible at a glance.
     *
     * @return list<TextColumn>
     */
    private static function countColumns(): array
    {
        return [
            TextColumn::make('menu1_count')
                ->label('Menu 1')
                ->summarize(Sum::make('sum')->label('Total')),

            TextColumn::make('menu2_count')
                ->label('Menu 2')
                ->summarize(Sum::make('sum')->label('Total')),

            TextColumn::make('total')
                ->label('Invités')
                ->state(fn (GuestReservation $record): int => $record->totalCount())
                ->summarize(
                    Summarizer::make('sum')
                        ->label('Total')
                        ->using(fn (QueryBuilder $query): int => (int) $query->sum(
                            DB::raw('menu1_count + menu2_count'),
                        )),
                ),
        ];
    }
}
