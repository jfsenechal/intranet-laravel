<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Minutes\Pages;

use AcMarche\Conseil\Filament\Resources\Minutes\MinuteResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateMinute extends CreateRecord
{
    #[Override]
    protected static string $resource = MinuteResource::class;

    public function getTitle(): string
    {
        return 'Nouveau procès-verbal';
    }
}
