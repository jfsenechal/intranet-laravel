<?php

namespace AcMarche\Document\Tables;

use AcMarche\Document\Filament\Resources\CategoryResource;
use AcMarche\Document\Models\Category;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoryTables
{
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Intitulé')
                    ->limit(120)
                    ->url(fn(Category $record) => CategoryResource::getUrl('view', ['record' => $record->id]))
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    }),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);

    }
}
