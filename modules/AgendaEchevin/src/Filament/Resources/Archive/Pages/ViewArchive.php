<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Filament\Resources\Archive\Pages;

use AcMarche\AgendaEchevin\Filament\Resources\Archive\ArchiveResource;
use AcMarche\AgendaEchevin\Filament\Resources\Archive\Schemas\ArchiveInfolist;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Override;

final class ViewArchive extends ViewRecord
{
    #[Override]
    protected static string $resource = ArchiveResource::class;

    public function getTitle(): string
    {
        return $this->record->title ?? 'Archive';
    }

    public function infolist(Schema $schema): Schema
    {
        return ArchiveInfolist::configure($schema);
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
