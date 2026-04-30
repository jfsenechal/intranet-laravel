<?php

declare(strict_types=1);

namespace AcMarche\Security\Filament\Resources\Users\Pages;

use AcMarche\Security\Filament\Resources\Users\UserResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditUser extends EditRecord
{
    #[Override]
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return $this->getRecord()->last_name.' '.$this->getRecord()->first_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->icon('tabler-eye'),
        ];
    }

    protected function getAllRelationManagers(): array
    {
        return [];
    }
}
