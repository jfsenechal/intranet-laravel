<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\StrategicObjective\Pages;

use AcMarche\Pst\Filament\Resources\StrategicObjective\StrategicObjectiveResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Override;

final class ViewStrategicObjective extends ViewRecord
{
    #[Override]
    protected static string $resource = StrategicObjectiveResource::class;

    public function getTitle(): string
    {
        return $this->record->name ?? 'Empty name';
    }

    public function getBreadcrumbs(): array
    {
        return [
            StrategicObjectiveResource::getUrl('index') => 'Objectifs Stratégiques',
            'OS',
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
}
