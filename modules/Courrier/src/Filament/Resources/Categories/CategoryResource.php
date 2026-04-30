<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Categories;

use AcMarche\Courrier\Filament\Resources\Categories\Pages\CreateCategory;
use AcMarche\Courrier\Filament\Resources\Categories\Pages\EditCategory;
use AcMarche\Courrier\Filament\Resources\Categories\Pages\ListCategory;
use AcMarche\Courrier\Filament\Resources\Categories\Pages\ViewCategory;
use AcMarche\Courrier\Filament\Resources\Categories\Schemas\CategoryForm;
use AcMarche\Courrier\Filament\Resources\Categories\Tables\CategoryTables;
use AcMarche\Courrier\Models\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class CategoryResource extends Resource
{
    #[Override]
    protected static ?string $model = Category::class;

    #[Override]
    protected static ?int $navigationSort = 6;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-rectangle-stack';

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Paramètres';

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
