<?php

namespace AcMarche\Category\Filament\Resources\CategoryResource\Pages;

use AcMarche\News\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    public static function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter une catégorie';
    }
}
