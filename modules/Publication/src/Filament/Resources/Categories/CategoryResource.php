<?php

declare(strict_types=1);

namespace AcMarche\Publication\Filament\Resources\Categories;

use AcMarche\Publication\Filament\Resources\Categories\Pages\CreateCategory;
use AcMarche\Publication\Filament\Resources\Categories\Pages\EditCategory;
use AcMarche\Publication\Filament\Resources\Categories\Pages\ListCategories;
use AcMarche\Publication\Filament\Resources\Categories\Pages\ViewCategory;
use AcMarche\Publication\Filament\Resources\Categories\Schemas\CategoryForm;
use AcMarche\Publication\Filament\Resources\Categories\Tables\CategoryTables;
use AcMarche\Publication\Models\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class CategoryResource extends Resource
{
    #[Override]
    protected static ?string $model = Category::class;

    #[Override]
    protected static ?int $navigationSort = 2;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-tag';

    #[Override]
    protected static ?string $navigationLabel = 'Categories';

    #[Override]
    protected static ?string $modelLabel = 'Category';

    #[Override]
    protected static ?string $pluralModelLabel = 'Categories';

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
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'view' => ViewCategory::route('/{record}'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
