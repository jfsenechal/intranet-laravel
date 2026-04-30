<?php

declare(strict_types=1);

namespace AcMarche\Security\Filament\Resources\Modules\Pages;

use AcMarche\Security\Filament\Resources\Modules\ModuleResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateModule extends CreateRecord
{
    #[Override]
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
