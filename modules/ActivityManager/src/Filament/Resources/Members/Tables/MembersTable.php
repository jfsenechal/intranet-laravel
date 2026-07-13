<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Members\Tables;

use AcMarche\ActivityManager\Enums\CiviliteEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('last_name')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('civility')
                    ->label('Civilité')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city')
                    ->label('Localité')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('mobile')
                    ->label('GSM')
                    ->toggleable()
                    ->copyable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->searchable(),
                IconColumn::make('enabled')
                    ->label('Actif')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('schedules_count')
                    ->counts('schedules')
                    ->label('Cours')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('registered_at')
                    ->label('Inscrit le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('civility')
                    ->label('Civilité')
                    ->options(CiviliteEnum::class),
                TernaryFilter::make('enabled')
                    ->label('Actif'),
                Filter::make('city')
                    ->schema([
                        TextInput::make('city')->label('Localité'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $query->when(
                        $data['city'] ?? null,
                        fn(Builder $q, string $value): Builder => $q->where('city', 'like', "%{$value}%"),
                    )),
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
