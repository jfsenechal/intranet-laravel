<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Employes\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class EmployesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('mail', 'asc')
            ->columns([
                TextColumn::make('mail')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sn')
                    ->label('Nom')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('givenName')
                    ->label('Prénom')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('samaccountname')
                    ->label('Identifiant')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('telephoneNumber')
                    ->label('Téléphone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_connection')
                    ->label('Dernière connexion')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sync_at')
                    ->label('Synchronisé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('last_connection')
                    ->label('Dernière connexion')
                    ->schema([
                        DatePicker::make('connected_from')
                            ->label('Dernière connexion avant le'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['connected_from'],
                        fn (Builder $query, string $date): Builder => $query->whereDate(
                            'last_connection',
                            '<=',
                            $date
                        )
                    )),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
