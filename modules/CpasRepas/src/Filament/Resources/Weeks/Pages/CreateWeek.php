<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\Weeks\Pages;

use AcMarche\CpasRepas\Filament\Resources\Weeks\WeekResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateWeek extends CreateRecord
{
    #[Override]
    protected static string $resource = WeekResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Add week';
    }
}
