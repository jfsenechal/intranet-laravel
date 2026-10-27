<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Citoyens\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class CitoyensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('sn', 'asc')
            ->columns([
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
                TextColumn::make('mail')
                    ->label('Email')
                    ->sortable()
                    ->searchable()
                    ->copyable(),
                TextColumn::make('uid')
                    ->label('Identifiant')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('l')
                    ->label('Localité')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gosaMailQuota')
                    ->label('Quota (Mo)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_connection')
                    ->label('Dernière connexion')
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('last_connection')
                    ->label('Dernière connexion')
                    ->schema([
                        DatePicker::make('connected_before')
                            ->label('Dernière connexion avant le'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['connected_before'],
                        fn (Builder $query, string $date): Builder => $query->whereDate(
                            'last_connection',
                            '<=',
                            $date
                        )
                    )),
                TernaryFilter::make('charter_accepted_at')
                    ->label('Charte acceptée')
                    ->nullable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
