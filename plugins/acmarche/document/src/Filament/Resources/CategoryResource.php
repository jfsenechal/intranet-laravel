<?php

namespace AcMarche\Document\Filament\Resources;

use AcMarche\Document\Filament\Resources\CategoryResource\Pages\CreateCategory;
use AcMarche\Document\Filament\Resources\CategoryResource\Pages\EditCategory;
use AcMarche\Document\Filament\Resources\CategoryResource\Pages\ListCategory;
use AcMarche\Document\Filament\Resources\CategoryResource\Pages\ViewCategory;
use AcMarche\Document\Form\CategoryForm;
use AcMarche\Document\Models\Category;
use AcMarche\Document\Tables\CategoryTables;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($form);
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
