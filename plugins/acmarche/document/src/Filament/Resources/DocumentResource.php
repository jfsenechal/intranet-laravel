<?php

namespace AcMarche\Document\Filament\Resources;

use AcMarche\Document\Filament\Resources\DocumentResource\Pages\CreateDocument;
use AcMarche\Document\Filament\Resources\DocumentResource\Pages\EditDocument;
use AcMarche\Document\Filament\Resources\DocumentResource\Pages\ListDocument;
use AcMarche\Document\Filament\Resources\DocumentResource\Pages\ViewDocument;
use AcMarche\Document\Form\DocumentForm;
use AcMarche\Document\Models\Document;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return DocumentForm::createForm($form);
    }

    public static function table(Table $table): Table
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
                    ->label('Catégorie')
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocument::route('/'),
            'create' => CreateDocument::route('/create'),
            'view' => ViewDocument::route('/{record}'),
            'edit' => EditDocument::route('/{record}/edit'),
        ];
    }
}
