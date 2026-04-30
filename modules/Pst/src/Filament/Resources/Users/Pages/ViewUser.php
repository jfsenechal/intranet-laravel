<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Users\Pages;

use AcMarche\Pst\Filament\Resources\Users\Schemas\UserInfolist;
use AcMarche\Pst\Filament\Resources\Users\UserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Override;
use STS\FilamentImpersonate\Actions\Impersonate;

final class ViewUser extends ViewRecord
{
    #[Override]
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return $this->record->full_name;
    }

    public function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon('tabler-edit'),
            Impersonate::make(),
        ];
    }

    protected function getAllRelationManagers(): array
    {
        return $this->getResource()::getRelations();
    }
}
