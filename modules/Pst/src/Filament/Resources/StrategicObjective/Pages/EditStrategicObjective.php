<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\StrategicObjective\Pages;

use AcMarche\Pst\Filament\Resources\StrategicObjective\StrategicObjectiveResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditStrategicObjective extends EditRecord
{
    #[Override]
    protected static string $resource = StrategicObjectiveResource::class;

    // force remove when edit
    public function getRelationManagers(): array
    {
        return [];
    }

    /**
     * to remove word "editer"
     */
    public function getTitle(): string
    {
        return $this->getRecord()->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
