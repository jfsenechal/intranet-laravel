<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Categories;

use AcMarche\CpasLibrary\Filament\Resources\Categories\Pages\CreateCategory;
use AcMarche\CpasLibrary\Filament\Resources\Categories\Pages\EditCategory;
use AcMarche\CpasLibrary\Filament\Resources\Categories\Pages\ListCategories;
use AcMarche\CpasLibrary\Filament\Resources\Categories\Pages\ViewCategory;
use AcMarche\CpasLibrary\Filament\Resources\Categories\RelationManagers\ChildrenRelationManager;
use AcMarche\CpasLibrary\Filament\Resources\Categories\RelationManagers\FichesRelationManager;
use AcMarche\CpasLibrary\Filament\Resources\Categories\Schemas\CategoryForm;
use AcMarche\CpasLibrary\Filament\Resources\Categories\Tables\CategoriesTable;
use AcMarche\CpasLibrary\Models\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class CategorieResource extends Resource
{
    #[Override]
    protected static ?string $model = Category::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Bibliothèque';

    #[Override]
    protected static ?int $navigationSort = 1;

    #[Override]
    protected static ?string $navigationLabel = 'Catégories';

    #[Override]
    protected static ?string $modelLabel = 'catégorie';

    #[Override]
    protected static ?string $pluralModelLabel = 'catégories';

    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'slug',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            FichesRelationManager::class,
            ChildrenRelationManager::class,
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
