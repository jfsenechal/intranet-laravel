<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Groups\Pages;

use AcMarche\SportsActivities\Filament\Resources\Groups\GroupResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditGroup extends EditRecord
{
    #[Override]
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
