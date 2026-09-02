<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\GuestReservations;

use AcMarche\MealDelivery\Filament\Resources\GuestReservations\Pages\CreateGuestReservation;
use AcMarche\MealDelivery\Filament\Resources\GuestReservations\Pages\EditGuestReservation;
use AcMarche\MealDelivery\Filament\Resources\GuestReservations\Pages\ListGuestReservations;
use AcMarche\MealDelivery\Filament\Resources\GuestReservations\Pages\ViewGuestReservation;
use AcMarche\MealDelivery\Filament\Resources\GuestReservations\Schemas\GuestReservationForm;
use AcMarche\MealDelivery\Filament\Resources\GuestReservations\Schemas\GuestReservationInfoList;
use AcMarche\MealDelivery\Filament\Resources\GuestReservations\Tables\GuestReservationTables;
use AcMarche\MealDelivery\Models\GuestReservation;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class GuestReservationResource extends Resource
{
    #[Override]
    protected static ?string $model = GuestReservation::class;

    #[Override]
    protected static ?int $navigationSort = 8;

    protected static string|UnitEnum|null $navigationGroup = 'Invités';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-user-group';
    }

    public static function getNavigationLabel(): string
    {
        return 'Réservations invités';
    }

    public static function form(Schema $schema): Schema
    {
        return GuestReservationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GuestReservationInfoList::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GuestReservationTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuestReservations::route('/'),
            'create' => CreateGuestReservation::route('/create'),
            'edit' => EditGuestReservation::route('/{record}/edit'),
            'view' => ViewGuestReservation::route('/{record}/view'),
        ];
    }
}
