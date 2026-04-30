<?php

declare(strict_types=1);

namespace AcMarche\Ad\Filament\Resources\Categories;

use AcMarche\Ad\Filament\Resources\Categories\Pages\CreateCategory;
use AcMarche\Ad\Filament\Resources\Categories\Pages\EditCategory;
use AcMarche\Ad\Filament\Resources\Categories\Pages\ListCategory;
use AcMarche\Ad\Filament\Resources\Categories\Pages\ViewCategory;
use AcMarche\Ad\Filament\Resources\Categories\RelationManagers\ClassifiedAdRelationManager;
use AcMarche\Ad\Filament\Resources\Categories\Schemas\CategoryForm;
use AcMarche\Ad\Filament\Resources\Categories\Tables\CategoryTables;
use AcMarche\Ad\Models\Category;
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
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-rectangle-stack';

    #[Override]
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
            ClassifiedAdRelationManager::class,
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
