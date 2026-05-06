<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Orders\Pages;

use AcMarche\MealDelivery\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateOrder extends CreateRecord
{
    #[Override]
    protected static string $resource = OrderResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Nouvelle commande';
    }
}
