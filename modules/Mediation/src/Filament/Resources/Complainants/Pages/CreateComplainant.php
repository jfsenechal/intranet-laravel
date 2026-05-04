<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\Complainants\Pages;

use AcMarche\Mediation\Filament\Resources\Complainants\ComplainantResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateComplainant extends CreateRecord
{
    #[Override]
    protected static string $resource = ComplainantResource::class;
}
