<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Services\Pages;

use AcMarche\Courrier\Filament\Resources\Services\Schemas\ServiceInfolist;
use AcMarche\Courrier\Filament\Resources\Services\ServiceResource;
use AcMarche\Courrier\Models\Service;
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
        return $this->record->name.' '.$this->record->initials;
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
                ->icon('tabler-trash')
                ->modalDescription(fn (Service $record): ?string => self::attachedMailsWarning(
                    $record->incomingMails()->count(),
                )),
        ];
    }

    /**
     * Warns that the courriers routed to the service will be unlinked. Returning
     * null leaves Filament's default confirmation text in place.
     */
    private static function attachedMailsWarning(int $count): ?string
    {
        if ($count === 0) {
            return null;
        }

        return $count === 1
            ? 'Ce service est lié à 1 courrier. Il sera détaché du service, mais ne sera pas supprimé. Voulez-vous continuer ?'
            : "Ce service est lié à {$count} courriers. Ils seront détachés du service, mais ne seront pas supprimés. Voulez-vous continuer ?";
    }
}
