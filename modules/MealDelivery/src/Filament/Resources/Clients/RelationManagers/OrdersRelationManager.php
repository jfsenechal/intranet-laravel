<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Clients\RelationManagers;

use AcMarche\MealDelivery\Filament\Resources\Clients\Tables\ClientTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class OrdersRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'orders';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Commandes ('.$ownerRecord->orders()->count().')';
    }

    /**
     * The panel makes relation managers read-only on resource view pages, which
     * denies every EditAction; the orders listed here are meant to be editable.
     */
    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return ClientTables::inline($table);
    }
}
