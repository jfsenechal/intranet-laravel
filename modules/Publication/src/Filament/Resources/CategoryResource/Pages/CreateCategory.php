<?php

namespace AcMarche\Publication\Filament\Resources\CategoryResource\Pages;

use AcMarche\Publication\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCategory extends CreateRecord
{
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
