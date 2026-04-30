<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Directions;

use AcMarche\Hrm\Filament\Resources\Directions\Pages\CreateDirection;
use AcMarche\Hrm\Filament\Resources\Directions\Pages\EditDirection;
use AcMarche\Hrm\Filament\Resources\Directions\Pages\ListDirections;
use AcMarche\Hrm\Filament\Resources\Directions\Pages\ViewDirection;
use AcMarche\Hrm\Filament\Resources\Directions\Schemas\DirectionForm;
use AcMarche\Hrm\Filament\Resources\Directions\Schemas\DirectionInfolist;
use AcMarche\Hrm\Filament\Resources\Directions\Tables\DirectionTables;
use AcMarche\Hrm\Models\Direction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class DirectionResource extends Resource
{
    #[Override]
    protected static ?string $model = Direction::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Configuration';

    #[Override]
    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-building-office';
    }

    public static function getNavigationLabel(): string
    {
        return 'Directions';
    }

    public static function getModelLabel(): string
    {
        return 'Direction';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Directions';
    }

    public static function form(Schema $schema): Schema
    {
        return DirectionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DirectionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DirectionTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDirections::route('/'),
            'create' => CreateDirection::route('/create'),
            'view' => ViewDirection::route('/{record}/view'),
            'edit' => EditDirection::route('/{record}/edit'),
        ];
    }
}
