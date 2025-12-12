<?php

namespace AcMarche\Courrier\Filament\Resources\RecipientResource\Pages;

use AcMarche\Courrier\Filament\Resources\RecipientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditRecipient extends EditRecord
{
    protected static string $resource = RecipientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
