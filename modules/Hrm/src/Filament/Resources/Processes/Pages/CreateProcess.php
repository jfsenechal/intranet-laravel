<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Processes\Pages;

use AcMarche\Hrm\Filament\Resources\Processes\ProcessResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateProcess extends CreateRecord
{
    #[Override]
    protected static string $resource = ProcessResource::class;

    #[Override]
    protected static ?string $title = 'Nouveau processus';
}
