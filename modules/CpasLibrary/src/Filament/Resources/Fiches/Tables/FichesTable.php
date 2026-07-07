<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Fiches\Tables;

use AcMarche\CpasLibrary\Models\Fiche;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
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
use Illuminate\Support\Facades\Storage;

final class FichesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('createdAt', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(60),
                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('tags.name')
                    ->label('Tags')
                    ->badge()
                    ->separator(',')
                    ->toggleable(),
                TextColumn::make('userAdd')
                    ->label('Auteur')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('mimeType')
                    ->label('Type')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('fileSize')
                    ->label('Taille')
                    ->numeric()
                    ->suffix(' o')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('createdAt')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('date_rappel')
                    ->label('Rappel')
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('tags')
                    ->label('Tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload(),
                Filter::make('userAdd')
                    ->schema([
                        TextInput::make('userAdd')->label('Auteur'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['userAdd'] ?? null,
                        fn (Builder $q, string $value) => $q->where('userAdd', $value),
                    )),
                TernaryFilter::make('has_rappel')
                    ->label('Avec rappel')
                    ->placeholder('Tous')
                    ->trueLabel('Avec rappel')
                    ->falseLabel('Sans rappel')
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('date_rappel'),
                        false: fn (Builder $q) => $q->whereNull('date_rappel'),
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
                Action::make('download')
                    ->label('Télécharger')
                    ->icon(Heroicon::ArrowDownTray)
                    ->visible(fn (Fiche $record): bool => $record->fileName !== null)
                    ->action(fn (Fiche $record) => Storage::disk('cpas-library')->download(
                        'fiches/'.$record->fileName,
                        $record->fileName,
                    )),
                DeleteAction::make()
                    ->label('Supprimer')
                    ->icon(Heroicon::Trash),
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
