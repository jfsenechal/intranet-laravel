<?php

namespace AcMarche\Mileage\Filament\Resources\Trips;

use AcMarche\Mileage\Filament\Resources\Trips\Pages;
use AcMarche\Mileage\Filament\Resources\Trips\Schemas\TripForm;
use AcMarche\Mileage\Filament\Resources\Trips\Tables\TripTables;
use AcMarche\Mileage\Models\Trip;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TripResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-map';
    }

    public static function getNavigationLabel(): string
    {
        return 'Mes déplacements';
    }

    public static function form(Schema $schema): Schema
    {
        return TripForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TripTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrips::route('/'),
            'create' => Pages\CreateTrip::route('/create'),
            'view' => Pages\ViewTrip::route('/{record}/view'),
            'edit' => Pages\EditTrip::route('/{record}/edit'),
        ];
    }
}
