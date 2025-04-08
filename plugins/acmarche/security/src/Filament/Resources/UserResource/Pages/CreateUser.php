<?php

namespace AcMarche\Security\Filament\Resources\UserResource\Pages;

use AcMarche\Security\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
