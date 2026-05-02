<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Filament\Resources\Event\Pages;

use AcMarche\AgendaEchevin\Filament\Resources\Event\EventResource;
use AcMarche\AgendaEchevin\Filament\Resources\Event\Schemas\EventInfolist;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Override;

final class ViewEvent extends ViewRecord
{
    #[Override]
    protected static string $resource = EventResource::class;

    public function getTitle(): string
    {
        return $this->record->title;
    }

    public function infolist(Schema $schema): Schema
    {
        return EventInfolist::configure($schema);
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
