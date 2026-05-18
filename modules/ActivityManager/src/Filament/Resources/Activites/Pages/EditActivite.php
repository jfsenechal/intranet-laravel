<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Activites\Pages;

use AcMarche\ActivityManager\Filament\Resources\Activites\ActiviteResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditActivite extends EditRecord
{
    #[Override]
    protected static string $resource = ActiviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
