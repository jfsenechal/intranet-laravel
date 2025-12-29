<?php

namespace AcMarche\News\Filament\Resources\Categories;

use AcMarche\News\Filament\Resources\Categories\Pages\CreateCategory;
use AcMarche\News\Filament\Resources\Categories\Pages\EditCategory;
use AcMarche\News\Filament\Resources\Categories\Pages\ListCategory;
use AcMarche\News\Filament\Resources\Categories\Pages\ViewCategory;
use AcMarche\News\Filament\Resources\Categories\Schemas\CategoryForm;
use AcMarche\News\Filament\Resources\Categories\Tables\CategoryTables;
use AcMarche\News\Models\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

final class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?int $navigationSort = 2;

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-rectangle-stack';

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
