<?php

declare(strict_types=1);

namespace AcMarche\Ad\Filament\Resources\Categories\Pages;

use AcMarche\Ad\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateCategory extends CreateRecord
{
    #[Override]
    protected static string $resource = CategoryResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter une catégorie';
    }
}
