<?php

namespace AcMarche\Mileage\Filament\Resources\BudgetArticle;

use AcMarche\Mileage\Filament\Resources\BudgetArticle\Schema\BudgetArticleForm;
use AcMarche\Mileage\Filament\Resources\BudgetArticle\Tables\BudgetArticleTables;
use AcMarche\Mileage\Models\BudgetArticle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class BudgetArticleResource extends Resource
{
    protected static ?string $model = BudgetArticle::class;

    protected static string|null|\UnitEnum $navigationGroup = 'Paramètres';

    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationLabel(): string
    {
        return 'Articles budgétaires';
    }

    public static function form(Schema $schema): Schema
    {
        return BudgetArticleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BudgetArticleTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBudgetArticles::route('/'),
            'create' => Pages\CreateBudgetArticle::route('/create'),
            'edit' => Pages\EditBudgetArticle::route('/{record}/edit'),
        ];
    }
}
