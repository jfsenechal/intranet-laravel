<?php

namespace AcMarche\Document\Tables;

use AcMarche\Document\Filament\Resources\DocumentResource;
use AcMarche\Document\Models\Document;
use Filament\Tables\Table;
use Filament\Tables;

class DocumentTables
{
    public static function table(Table $table)
    {
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Intitulé')
                    ->url(fn(Document $record) => DocumentResource::getUrl('view', ['record' => $record->id])),
                Tables\Columns\TextColumn::make('category')
                    ->searchable()
                    ->label('Catégorie'),
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
