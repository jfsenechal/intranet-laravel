<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Tags\Pages;

use AcMarche\CpasLibrary\Filament\Resources\Tags\TagResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateTag extends CreateRecord
{
    #[Override]
    protected static string $resource = TagResource::class;

    public function getTitle(): string
    {
        return 'Nouveau tag';
    }
}
