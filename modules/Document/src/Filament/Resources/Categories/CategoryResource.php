<?php

namespace AcMarche\Document\Filament\Resources\Categories;

use AcMarche\Document\Filament\Resources\Categories\Pages\CreateCategory;
use AcMarche\Document\Filament\Resources\Categories\Pages\EditCategory;
use AcMarche\Document\Filament\Resources\Categories\Pages\ListCategory;
use AcMarche\Document\Filament\Resources\Categories\Pages\ViewCategory;
use AcMarche\Document\Filament\Resources\Categories\Schemas\CategoryForm;
use AcMarche\Document\Filament\Resources\Categories\Tables\CategoryTables;
use AcMarche\Document\Models\Category;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?int $navigationSort = 2;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoryTables::table($table);
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
