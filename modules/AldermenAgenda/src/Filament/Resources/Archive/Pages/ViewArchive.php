<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Archive\Pages;

use AcMarche\AldermenAgenda\Filament\Resources\Archive\ArchiveResource;
use AcMarche\AldermenAgenda\Filament\Resources\Archive\Schemas\ArchiveInfolist;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Override;

final class ViewArchive extends ViewRecord
{
    #[Override]
    protected static string $resource = ArchiveResource::class;

    public function getTitle(): string
    {
        return $this->record->name ?? 'Archive';
    }

    public function infolist(Schema $schema): Schema
    {
        return ArchiveInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
