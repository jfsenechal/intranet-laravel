<?php

namespace AcMarche\Courrier\Filament\Resources\CategoryResource\Pages;

use AcMarche\Courrier\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
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
