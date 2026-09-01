<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Clients\RelationManagers;

use AcMarche\MealDelivery\Filament\Resources\GuestReservations\Tables\GuestReservationTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class GuestReservationsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'guestReservations';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Repas invités ('.$ownerRecord->guestReservations()->count().')';
    }

    /**
     * Only clients eating at the cafeteria can receive family for a meal.
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return (bool) $ownerRecord->use_cafeteria;
    }

    /**
     * The panel makes relation managers read-only on resource view pages, which
     * denies every EditAction; these reservations are meant to stay editable.
     */
    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return GuestReservationTables::inline($table);
    }
}
