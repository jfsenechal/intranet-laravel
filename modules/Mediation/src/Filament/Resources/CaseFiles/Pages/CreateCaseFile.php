<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\CaseFiles\Pages;

use AcMarche\Mediation\Filament\Resources\CaseFiles\CaseFileResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateCaseFile extends CreateRecord
{
    #[Override]
    protected static string $resource = CaseFileResource::class;
}
