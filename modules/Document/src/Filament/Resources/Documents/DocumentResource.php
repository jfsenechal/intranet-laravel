<?php

declare(strict_types=1);

namespace AcMarche\Document\Filament\Resources\Documents;

use AcMarche\Document\Filament\Resources\Documents\Pages\CreateDocument;
use AcMarche\Document\Filament\Resources\Documents\Pages\EditDocument;
use AcMarche\Document\Filament\Resources\Documents\Pages\ListDocuments;
use AcMarche\Document\Filament\Resources\Documents\Pages\ViewDocument;
use AcMarche\Document\Filament\Resources\Documents\Schemas\DocumentForm;
use AcMarche\Document\Filament\Resources\Documents\Tables\DocumentTables;
use AcMarche\Document\Models\Document;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class DocumentResource extends Resource
{
    #[Override]
    protected static ?string $model = Document::class;

    #[Override]
    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string
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
            'index' => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'view' => ViewDocument::route('/{record}/view'),
            'edit' => EditDocument::route('/{record}/edit'),
        ];
    }
}
