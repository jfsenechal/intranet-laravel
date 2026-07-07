<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Categories\Pages;

use AcMarche\CpasLibrary\Filament\Resources\Categories\CategorieResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateCategory extends CreateRecord
{
    #[Override]
    protected static string $resource = CategorieResource::class;

    public function getTitle(): string
    {
        return 'Nouvelle catégorie';
    }
}
