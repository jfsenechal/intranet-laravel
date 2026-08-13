<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\OffenseActs\Pages;

use AcMarche\Offenses\Filament\Resources\OffenseActs\OffenseActResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewOffenseAct extends ViewRecord
{
    #[Override]
    protected static string $resource = OffenseActResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    /**
     * The act only carries its name, which is already the page title, so the
     * page shows the offenses it is linked to instead of the disabled form
     * Filament falls back to when a resource has no infolist.
     */
    #[Override]
    public function content(Schema $schema): Schema
    {
        return $schema->components([
            $this->getRelationManagersContentComponent(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->icon(Heroicon::Pencil),
            OffenseActResource::deleteAction(),
        ];
    }
}
