<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\Orders\Pages;

use AcMarche\CpasRepas\Filament\Resources\Orders\OrderResource;
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
        return 'Add order';
    }
}
