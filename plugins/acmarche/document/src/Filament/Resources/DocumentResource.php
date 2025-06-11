<?php

namespace AcMarche\Document\Filament\Resources;

use AcMarche\Document\Filament\Resources\DocumentResource\Pages\CreateDocument;
use AcMarche\Document\Filament\Resources\DocumentResource\Pages\EditDocument;
use AcMarche\Document\Filament\Resources\DocumentResource\Pages\ListDocument;
use AcMarche\Document\Filament\Resources\DocumentResource\Pages\ViewDocument;
use AcMarche\Document\Form\DocumentForm;
use AcMarche\Document\Models\Document;
use AcMarche\Document\Tables\DocumentTables;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $form): Schema
    {
        return DocumentForm::createForm($form);
    }

    public static function table(Table $table): Table
    {
        return DocumentTables::table($table);
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
