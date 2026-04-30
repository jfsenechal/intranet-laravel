<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Partner\Pages;

use AcMarche\Pst\Filament\Resources\Partner\PartnerResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreatePartner extends CreateRecord
{
    #[Override]
    protected static string $resource = PartnerResource::class;
}
