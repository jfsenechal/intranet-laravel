<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Filament\Resources\Participation\Pages;

use AcMarche\AgendaEchevin\Filament\Resources\Participation\ParticipationResource;
use AcMarche\AgendaEchevin\Filament\Resources\Participation\Schemas\ParticipationInfolist;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Override;

final class ViewParticipation extends ViewRecord
{
    #[Override]
    protected static string $resource = ParticipationResource::class;

    public function getTitle(): string
    {
        return $this->record->event->title;
    }

    public function infolist(Schema $schema): Schema
    {
        return ParticipationInfolist::configure($schema);
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
