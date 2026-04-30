<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Trips;

use AcMarche\Mileage\Filament\Resources\Trips\Pages\CreateTrip;
use AcMarche\Mileage\Filament\Resources\Trips\Pages\EditTrip;
use AcMarche\Mileage\Filament\Resources\Trips\Pages\ListTrips;
use AcMarche\Mileage\Filament\Resources\Trips\Pages\ViewTrip;
use AcMarche\Mileage\Filament\Resources\Trips\Schemas\TripForm;
use AcMarche\Mileage\Filament\Resources\Trips\Tables\TripTables;
use AcMarche\Mileage\Models\Trip;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class TripResource extends Resource
{
    #[Override]
    protected static ?string $model = Trip::class;

    #[Override]
    protected static ?int $navigationSort = 1;

    #[Override]
    protected static ?string $modelLabel = 'Déplacement';

    #[Override]
    protected static ?string $pluralModelLabel = 'Déplacements';

    public static function getNavigationIcon(): string
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
            'index' => ListTrips::route('/'),
            'create' => CreateTrip::route('/create'),
            'view' => ViewTrip::route('/{record}/view'),
            'edit' => EditTrip::route('/{record}/edit'),
        ];
    }
}
