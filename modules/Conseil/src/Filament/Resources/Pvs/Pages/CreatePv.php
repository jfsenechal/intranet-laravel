<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Pvs\Pages;

use AcMarche\Conseil\Filament\Resources\Pvs\PvResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreatePv extends CreateRecord
{
    #[Override]
    protected static string $resource = PvResource::class;

    public function getTitle(): string
    {
        return 'Nouveau procès-verbal';
    }
}
