<?php

namespace AcMarche\Mileage\Filament\Resources\Users\Pages;

use AcMarche\Mileage\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

final class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string|Htmlable
    {
        return "Modification d'un utilisateur";
    }

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
