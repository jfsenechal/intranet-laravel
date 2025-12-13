<?php

namespace AcMarche\News\Filament\Resources\Categories\Tables;

use AcMarche\News\Filament\Resources\Categories\CategoryResource;
use AcMarche\News\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class CategoryTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label('Intitulé')
                    ->limit(120)
                    ->url(fn (Category $record) => CategoryResource::getUrl('view', ['record' => $record->id]))
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (mb_strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    }),
                Tables\Columns\ColorColumn::make('color')
                    ->searchable()
                    ->label('Couleur'),
                TextColumn::make('count_news')
                    ->label('Actus'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
