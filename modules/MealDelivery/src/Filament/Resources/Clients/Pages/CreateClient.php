<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Clients\Pages;

use AcMarche\MealDelivery\Filament\Resources\Clients\ClientResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateClient extends CreateRecord
{
    #[Override]
    protected static string $resource = ClientResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Add client';
    }
}
