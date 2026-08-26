<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Articles;

use AcMarche\App\Filament\Resources\Articles\Pages\CreateArticle;
use AcMarche\App\Filament\Resources\Articles\Pages\EditArticle;
use AcMarche\App\Filament\Resources\Articles\Pages\ListArticles;
use AcMarche\App\Filament\Resources\Articles\Pages\ViewArticle;
use AcMarche\App\Filament\Resources\Articles\Schemas\ArticleForm;
use AcMarche\App\Filament\Resources\Articles\Schemas\ArticleInfolist;
use AcMarche\App\Filament\Resources\Articles\Tables\ArticleTables;
use AcMarche\App\Models\Article;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class ArticleResource extends Resource
{
    #[Override]
    protected static ?string $model = Article::class;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedNewspaper;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Administration';

    #[Override]
    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return 'Articles';
    }

    public static function getModelLabel(): string
    {
        return 'article';
    }

    public static function getPluralModelLabel(): string
    {
        return 'articles';
    }

    public static function form(Schema $schema): Schema
    {
        return ArticleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ArticleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArticleTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticles::route('/'),
            'create' => CreateArticle::route('/create'),
            'view' => ViewArticle::route('/{record}'),
            'edit' => EditArticle::route('/{record}/edit'),
        ];
    }
}
