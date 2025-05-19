<?php

namespace AcMarche\News\Filament\Resources;

use AcMarche\News\Filament\Resources\CategoryResource\Pages\CreateCategory;
use AcMarche\News\Filament\Resources\CategoryResource\Pages\EditCategory;
use AcMarche\News\Filament\Resources\CategoryResource\Pages\ListCategory;
use AcMarche\News\Filament\Resources\CategoryResource\Pages\ViewCategory;
use AcMarche\News\Form\CategoryForm;
use AcMarche\News\Models\Category;
use AcMarche\News\Tables\CategoryTables;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Paramètres';

    protected static ?string $navigationLabel = 'Catégories';

    public static function form(Form $form): Form
    {
        return CategoryForm::createForm($form);
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
