<?php

declare(strict_types=1);

namespace AcMarche\News\Filament\Resources\News;

use AcMarche\News\Filament\Resources\News\Pages\CreateNews;
use AcMarche\News\Filament\Resources\News\Pages\EditNews;
use AcMarche\News\Filament\Resources\News\Pages\ListNews;
use AcMarche\News\Filament\Resources\News\Pages\ViewNews;
use AcMarche\News\Filament\Resources\News\Schemas\NewsForm;
use AcMarche\News\Filament\Resources\News\Tables\NewsTables;
use AcMarche\News\Models\News;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class NewsResource extends Resource
{
    #[Override]
    protected static ?string $model = News::class;

    #[Override]
    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-newspaper';
    }

    public static function getNavigationLabel(): string
    {
        return 'L\'actuatlité';
    }

    public static function form(Schema $schema): Schema
    {
        return NewsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NewsTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNews::route('/'),
            'create' => CreateNews::route('/create'),
            'edit' => EditNews::route('/{record}/edit'),
            'view' => ViewNews::route('/{record}'),
        ];
    }
}
