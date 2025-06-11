<?php

namespace AcMarche\Security\Filament\Resources\ModuleResource\Pages;

use AcMarche\App\Filament\RedirectTrait;
use AcMarche\Security\Filament\Resources\ModuleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateModule extends CreateRecord
{
    use RedirectTrait;

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
