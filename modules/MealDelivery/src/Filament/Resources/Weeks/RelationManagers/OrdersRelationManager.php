<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks\RelationManagers;

use AcMarche\MealDelivery\Filament\Resources\Orders\Tables\OrderTables;
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
        return 'Commandes';
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return OrderTables::relation($table);
    }
}
