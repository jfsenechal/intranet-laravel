<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Rsses;

use AcMarche\App\Filament\Resources\Rsses\Pages\CreateRss;
use AcMarche\App\Filament\Resources\Rsses\Pages\EditRss;
use AcMarche\App\Filament\Resources\Rsses\Pages\ListRsses;
use AcMarche\App\Filament\Resources\Rsses\Schemas\RssForm;
use AcMarche\App\Filament\Resources\Rsses\Tables\RssTables;
use AcMarche\App\Models\Rss;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

final class RssResource extends Resource
{
    #[Override]
    protected static ?string $model = Rss::class;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-rss';

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Mon profil';

    public static function getNavigationLabel(): string
    {
        return 'Mes flux RSS';
    }

    public static function getModelLabel(): string
    {
        return 'Flux RSS';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Flux RSS';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::id());
    }

    public static function form(Schema $schema): Schema
    {
        return RssForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RssTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRsses::route('/'),
            'create' => CreateRss::route('/create'),
            'edit' => EditRss::route('/{record}/edit'),
        ];
    }
}
