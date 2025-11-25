<?php

namespace AcMarche\News\Filament\Resources;

use AcMarche\News\Filament\Resources\CategoryResource\Pages\CreateCategory;
use AcMarche\News\Filament\Resources\CategoryResource\Pages\EditCategory;
use AcMarche\News\Filament\Resources\CategoryResource\Pages\ListCategory;
use AcMarche\News\Filament\Resources\CategoryResource\Pages\ViewCategory;
use AcMarche\News\Filament\Resources\CategoryResource\Schema\CategoryForm;
use AcMarche\News\Filament\Resources\CategoryResource\Tables\CategoryTables;
use AcMarche\News\Models\Category;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?int $navigationSort = 2;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Catégories';

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoryTables::configure($table);
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
            'index' => ListCategory::route('/'),
            'create' => CreateCategory::route('/create'),
            'view' => ViewCategory::route('/{record}'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
