<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Folders\Tables;

use AcMarche\Agent\Models\Folder;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class FolderTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $query->with('parent.parent.parent.parent');

                return $query->orderByRaw(
                    "CONCAT_WS('/',
                        (SELECT p4.name FROM folders p4 WHERE p4.id = (
                            SELECT p3b.parent_id FROM folders p3b WHERE p3b.id = (
                                SELECT p2b.parent_id FROM folders p2b WHERE p2b.id = folders.parent_id
                            )
                        )),
                        (SELECT p3.name FROM folders p3 WHERE p3.id = (
                            SELECT p2a.parent_id FROM folders p2a WHERE p2a.id = folders.parent_id
                        )),
                        (SELECT p2.name FROM folders p2 WHERE p2.id = folders.parent_id),
                        folders.name
                    )"
                );
            })
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->formatStateUsing(function (Folder $record): string {
                        $depth = 0;
                        $current = $record->parent;
                        while ($current !== null) {
                            $depth++;
                            $current = $current->parent;
                        }

                        return $depth === 0
                            ? $record->name
                            : str_repeat('—', $depth).' '.$record->name;
                    }),
                TextColumn::make('profiles_count')
                    ->counts('profiles')
                    ->label('Profils')
                    ->badge(),
                TextColumn::make('updated_at')
                    ->label('Modifié')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->label('Parent')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
