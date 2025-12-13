<?php

namespace AcMarche\News\Filament\Resources\News;

use AcMarche\News\Filament\Resources\News\Pages;
use AcMarche\News\Filament\Resources\News\Schemas\NewsForm;
use AcMarche\News\Filament\Resources\News\Tables\NewsTables;
use AcMarche\News\Models\News;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): ?string
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
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
            'view' => Pages\ViewNews::route('/{record}'),
        ];
    }
}
