<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Pvs\Pages;

use AcMarche\Conseil\Filament\Resources\Pvs\PvResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditPv extends EditRecord
{
    #[Override]
    protected static string $resource = PvResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
