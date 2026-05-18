<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Membres\Pages;

use AcMarche\ActivityManager\Filament\Resources\Membres\MembreResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditMembre extends EditRecord
{
    #[Override]
    protected static string $resource = MembreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
