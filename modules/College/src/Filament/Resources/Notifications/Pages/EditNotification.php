<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Notifications\Pages;

use AcMarche\College\Filament\Resources\Notifications\NotificationResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditNotification extends EditRecord
{
    #[Override]
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
