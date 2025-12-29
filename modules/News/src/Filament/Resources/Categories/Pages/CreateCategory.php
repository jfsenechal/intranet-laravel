<?php

namespace AcMarche\News\Filament\Resources\Categories\Pages;

use AcMarche\News\Filament\Resources\Categories\CategoryResource;
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
