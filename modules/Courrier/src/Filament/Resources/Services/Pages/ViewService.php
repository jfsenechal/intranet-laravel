<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Services\Pages;

use AcMarche\Courrier\Filament\Resources\Services\Schemas\ServiceInfolist;
use AcMarche\Courrier\Filament\Resources\Services\ServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Override;

final class ViewService extends ViewRecord
{
    #[Override]
    protected static string $resource = ServiceResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return ServiceInfolist::configure($schema);
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
