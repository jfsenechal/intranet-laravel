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
            ->defaultSort('nom')
            ->columns([
                TextColumn::make('civilite')
                    ->label('Civilité')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('prenom')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('localite')
                    ->label('Localité')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('gsm')
                    ->label('GSM')
                    ->toggleable()
                    ->copyable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->toggleable()
                    ->copyable()
                    ->searchable(),
                IconColumn::make('enabled')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('cours_count')
                    ->counts('cours')
                    ->label('Cours')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('inscrit_le')
                    ->label('Inscrit le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('civilite')
                    ->label('Civilité')
                    ->options(CiviliteEnum::class),
                TernaryFilter::make('enabled')
                    ->label('Actif'),
                Filter::make('localite')
                    ->schema([
                        TextInput::make('localite')->label('Localité'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['localite'] ?? null,
                        fn (Builder $q, string $value): Builder => $q->where('localite', 'like', "%{$value}%"),
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
