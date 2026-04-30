<?php

declare(strict_types=1);

namespace AcMarche\Security\Filament\Resources\Modules\Pages;

use AcMarche\Security\Filament\Resources\Modules\ModuleResource;
use AcMarche\Security\Filament\Resources\Modules\RelationManagers\UserRelationManager;
use AcMarche\Security\Filament\Resources\Modules\Schemas\ModuleInfolist;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Override;

final class ViewModule extends ViewRecord
{
    #[Override]
    protected static string $resource = ModuleResource::class;

    public function getTitle(): string
    {
        return 'Module '.$this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return ModuleInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon('tabler-edit'),
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }

    protected function getAllRelationManagers(): array
    {
        $relations = $this->getResource()::getRelations();
        array_unshift($relations, UserRelationManager::class);

        return $relations;
    }
}
