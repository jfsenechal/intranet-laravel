<?php

namespace AcMarche\Security\Filament\Resources\UserResource\Pages;

use AcMarche\Security\Filament\Resources\UserResource;
use Filament\Actions;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->last_name. ' ' . $this->getRecord()->first_name;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}
