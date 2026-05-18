<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Cours\Pages;

use AcMarche\ActivityManager\Filament\Resources\Cours\CoursResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateCours extends CreateRecord
{
    #[Override]
    protected static string $resource = CoursResource::class;

    public function getTitle(): string
    {
        return 'Nouveau cours';
    }
}
