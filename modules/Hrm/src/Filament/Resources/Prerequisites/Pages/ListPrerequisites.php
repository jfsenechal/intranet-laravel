<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Prerequisites\Pages;

use AcMarche\Hrm\Filament\Resources\Prerequisites\PrerequisiteResource;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListPrerequisites extends ListRecords
{
    #[Override]
    protected static string $resource = PrerequisiteResource::class;
}
