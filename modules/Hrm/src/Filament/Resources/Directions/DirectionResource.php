<?php

namespace AcMarche\Hrm\Filament\Resources\Directions;

use AcMarche\Hrm\Filament\Resources\Directions\Schemas\DirectionForm;
use AcMarche\Hrm\Filament\Resources\Directions\Tables\DirectionTables;
use AcMarche\Hrm\Models\Direction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

final class DirectionResource extends Resource
{
    protected static ?string $model = Direction::class;

    protected static string|null|\UnitEnum $navigationGroup = 'Organisation';

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): ?string
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

    public static function table(Table $table): Table
    {
        return DirectionTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDirections::route('/'),
            'create' => Pages\CreateDirection::route('/create'),
            'view' => Pages\ViewDirection::route('/{record}/view'),
            'edit' => Pages\EditDirection::route('/{record}/edit'),
        ];
    }
}
