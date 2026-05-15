<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Groupes\Pages;

use AcMarche\SportsActivities\Filament\Resources\Groupes\GroupeResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditGroupe extends EditRecord
{
    #[Override]
    protected static string $resource = GroupeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
