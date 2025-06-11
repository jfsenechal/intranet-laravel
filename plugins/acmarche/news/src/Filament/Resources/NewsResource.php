<?php

namespace AcMarche\News\Filament\Resources;

use AcMarche\News\Filament\Resources\NewsResource\Pages\CreateNews;
use AcMarche\News\Filament\Resources\NewsResource\Pages\EditNews;
use AcMarche\News\Filament\Resources\NewsResource\Pages\ListNews;
use AcMarche\News\Filament\Resources\NewsResource\Pages\ViewNews;
use AcMarche\News\Form\NewsForm;
use AcMarche\News\Models\News;
use AcMarche\News\Tables\NewsTables;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'L\'actualité';

    public static function form(Schema $form): Schema
    {
        return NewsForm::createForm($form);
    }

    public static function table(Table $table): Table
    {
        return NewsTables::table($table);
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
            'index' => ListNews::route('/'),
            'create' => CreateNews::route('/create'),
            'view' => ViewNews::route('/{record}'),
            'edit' => EditNews::route('/{record}/edit'),
        ];
    }
}
