<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Recipients\Pages;

use AcMarche\College\Filament\Resources\Recipients\RecipientResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditRecipient extends EditRecord
{
    #[Override]
    protected static string $resource = RecipientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
