<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Filament\Resources\Telephones\Pages;

use AcMarche\Telecommunication\Filament\Resources\Telephones\TelephoneResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditTelephone extends EditRecord
{
    #[Override]
    protected static string $resource = TelephoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
