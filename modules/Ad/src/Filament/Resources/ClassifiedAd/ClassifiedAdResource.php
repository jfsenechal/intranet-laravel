<?php

declare(strict_types=1);

namespace AcMarche\Ad\Filament\Resources\ClassifiedAd;

use AcMarche\Ad\Filament\Resources\ClassifiedAd\Pages\CreateClassifiedAd;
use AcMarche\Ad\Filament\Resources\ClassifiedAd\Pages\EditClassifiedAd;
use AcMarche\Ad\Filament\Resources\ClassifiedAd\Pages\ListClassifiedAd;
use AcMarche\Ad\Filament\Resources\ClassifiedAd\Pages\ViewClassifiedAd;
use AcMarche\Ad\Filament\Resources\ClassifiedAd\Schemas\ClassifiedAdForm;
use AcMarche\Ad\Filament\Resources\ClassifiedAd\Tables\ClassifiedAdTables;
use AcMarche\Ad\Models\ClassifiedAd;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class ClassifiedAdResource extends Resource
{
    #[Override]
    protected static ?string $model = ClassifiedAd::class;

    #[Override]
    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-megaphone';
    }

    public static function getNavigationLabel(): string
    {
        return 'Les annonces';
    }

    public static function form(Schema $schema): Schema
    {
        return ClassifiedAdForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClassifiedAdTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClassifiedAd::route('/'),
            'create' => CreateClassifiedAd::route('/create'),
            'edit' => EditClassifiedAd::route('/{record}/edit'),
            'view' => ViewClassifiedAd::route('/{record}'),
        ];
    }
}
