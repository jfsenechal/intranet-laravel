<?php

namespace AcMarche\Security\Filament\Resources\Modules\Pages;

use AcMarche\Security\Filament\Resources\Modules\ModuleResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateModule extends CreateRecord
{
    protected static string $resource = ModuleResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter un module';
    }
}
