<?php

namespace AcMarche\News\Filament\Resources\NewsResource\Pages;

use AcMarche\News\Filament\Resources\NewsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNews extends CreateRecord
{
    protected static string $resource = NewsResource::class;

    public static function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter une actualité';
    }
}
