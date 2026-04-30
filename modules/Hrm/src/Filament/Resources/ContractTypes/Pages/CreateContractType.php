<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\ContractTypes\Pages;

use AcMarche\Hrm\Filament\Resources\ContractTypes\ContractTypeResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateContractType extends CreateRecord
{
    #[Override]
    protected static string $resource = ContractTypeResource::class;
}
