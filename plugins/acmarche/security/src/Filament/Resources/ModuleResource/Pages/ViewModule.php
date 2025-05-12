<?php

namespace AcMarche\Security\Filament\Resources\ModuleResource\Pages;

use AcMarche\Security\Filament\Resources\ModuleResource;
use AcMarche\Security\Filament\Resources\ModuleResource\RelationManagers\UserRelationManager;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ContentTabPosition;
use Filament\Resources\Pages\ViewRecord;

class ViewModule extends ViewRecord
{
    protected static string $resource = ModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('tabler-edit'),
            Actions\DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }

    public function getTitle(): string
    {
        return 'Module '.$this->record->name;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('description')
                    ->label(false),
                TextEntry::make('is_public')
                    ->label('Publique?'),
                TextEntry::make('is_external')
                    ->label('Externe?'),
                TextEntry::make('url'),
            ]);
    }

    protected function getAllRelationManagers(): array
    {
        $relations = $this->getResource()::getRelations();
        array_unshift($relations, UserRelationManager::class);

        return $relations;
    }
}
