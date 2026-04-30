<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\PayScales\Pages;

use AcMarche\Hrm\Filament\Resources\PayScales\PayScaleResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreatePayScale extends CreateRecord
{
    #[Override]
    protected static string $resource = PayScaleResource::class;
}
