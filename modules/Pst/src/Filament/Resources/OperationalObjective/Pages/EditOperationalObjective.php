<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\OperationalObjective\Pages;

use AcMarche\Pst\Filament\Resources\OperationalObjective\OperationalObjectiveResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditOperationalObjective extends EditRecord
{
    #[Override]
    protected static string $resource = OperationalObjectiveResource::class;

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
