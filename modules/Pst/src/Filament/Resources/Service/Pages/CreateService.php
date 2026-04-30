<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Service\Pages;

use AcMarche\Pst\Filament\Resources\Service\ServiceResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateService extends CreateRecord
{
    #[Override]
    protected static string $resource = ServiceResource::class;

    #[Override]
    protected static ?string $title = 'Ajout d\'un service';
}
