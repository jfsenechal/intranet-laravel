<?php

namespace AcMarche\Document\Filament\Resources\Documents;

use AcMarche\Document\Filament\Resources\Documents\Schemas\DocumentForm;
use AcMarche\Document\Filament\Resources\Documents\Tables\DocumentTables;
use AcMarche\Document\Models\Document;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

final class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationLabel(): string
    {
        return 'Documents';
    }

    public static function form(Schema $schema): Schema
    {
        return DocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'view' => Pages\ViewDocument::route('/{record}/view'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
