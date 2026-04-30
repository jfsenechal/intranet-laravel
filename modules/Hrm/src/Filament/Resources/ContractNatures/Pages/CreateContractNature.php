<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\ContractNatures\Pages;

use AcMarche\Hrm\Filament\Resources\ContractNatures\ContractNatureResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateContractNature extends CreateRecord
{
    #[Override]
    protected static string $resource = ContractNatureResource::class;
}
