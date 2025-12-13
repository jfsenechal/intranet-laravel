<?php

namespace AcMarche\Security\Filament\Resources\Users\Pages;

use AcMarche\Security\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Ajouter un utilisateur';
    }
}
