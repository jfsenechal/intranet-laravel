<?php

declare(strict_types=1);

namespace AcMarche\Publication\Filament\Resources\Categories\Pages;

use AcMarche\Publication\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Override;

final class ViewCategory extends ViewRecord
{
    #[Override]
    protected static string $resource = CategoryResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
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
