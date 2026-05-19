<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Profiles\Pages;

use AcMarche\Agent\Filament\Resources\Profiles\ProfileResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListProfiles extends ListRecords
{
    #[Override]
    protected static string $resource = ProfileResource::class;

    #[Override]
    protected static ?string $title = 'Liste des profils';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addProfileInfo')
                ->label('Ajouter un profil')
                ->icon('tabler-plus')
                ->modalHeading('Ajouter un profil')
                ->modalDescription('Pour ajouter un nouveau profil, la demande doit être effectuée par les ressources humaines.')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer'),
        ];
    }
}
