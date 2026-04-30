<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\OperationalObjective\Pages;

use AcMarche\Pst\Filament\Resources\OperationalObjective\OperationalObjectiveResource;
use AcMarche\Pst\Filament\Resources\OperationalObjective\RelationManagers\ActionsRelationManager;
use AcMarche\Pst\Filament\Resources\StrategicObjective\StrategicObjectiveResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Override;

final class ViewOperationalObjective extends ViewRecord
{
    #[Override]
    protected static string $resource = OperationalObjectiveResource::class;

    public function getTitle(): string
    {
        return $this->record->name ?? 'Empty name';
    }

    public function getBreadcrumbs(): array
    {
        $parent = $this->record->strategicObjective()->first();

        return [
            StrategicObjectiveResource::getUrl('index') => 'Objectifs Stratégiques',
            StrategicObjectiveResource::getUrl('view', ['record' => $parent]) => $parent->name,
            'OO',
            // $this->getBreadcrumb(),
        ];
    }

    /**
     * no form in view
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([]);
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
        $relations[] = ActionsRelationManager::class;

        return $relations;
    }
}
