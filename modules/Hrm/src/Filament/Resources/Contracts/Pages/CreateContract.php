<?php

namespace AcMarche\Hrm\Filament\Resources\Contracts\Pages;

use AcMarche\Hrm\Filament\Resources\Contracts\ContractResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateContract extends CreateRecord
{
    protected static string $resource = ContractResource::class;
}
