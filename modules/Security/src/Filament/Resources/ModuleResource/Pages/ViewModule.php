<?php

namespace AcMarche\Security\Filament\Resources\ModuleResource\Pages;

use AcMarche\Security\Filament\Resources\ModuleResource;
use AcMarche\Security\Filament\Resources\ModuleResource\RelationManagers\UserRelationManager;
use AcMarche\Security\Filament\Resources\ModuleResource\Schema\ModuleInfolist;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

final class ViewModule extends ViewRecord
{
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
            Actions\EditAction::make()
                ->icon('tabler-edit'),
            Actions\DeleteAction::make()
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
