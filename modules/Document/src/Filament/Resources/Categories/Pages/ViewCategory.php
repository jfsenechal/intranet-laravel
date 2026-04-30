<?php

declare(strict_types=1);

namespace AcMarche\Document\Filament\Resources\Categories\Pages;

use AcMarche\Document\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Override;

final class ViewCategory extends ViewRecord
{
    #[Override]
    protected static string $resource = CategoryResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    /**
     * Because infolist is defined with empty schema, we need to override this method
     */
    public function hasInfolist(): bool
    {
        return true;
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
