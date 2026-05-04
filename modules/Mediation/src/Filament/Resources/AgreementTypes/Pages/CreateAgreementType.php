<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\AgreementTypes\Pages;

use AcMarche\Mediation\Filament\Resources\AgreementTypes\AgreementTypeResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateAgreementType extends CreateRecord
{
    #[Override]
    protected static string $resource = AgreementTypeResource::class;
}
