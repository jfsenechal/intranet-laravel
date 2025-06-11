<?php

namespace AcMarche\Document\Tables;

use AcMarche\Document\Filament\Resources\DocumentResource;
use AcMarche\Document\Models\Document;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

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
