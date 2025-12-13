<?php

namespace AcMarche\Document\Filament\Resources\Documents\Pages;

use AcMarche\Document\Filament\Resources\Documents\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }
}
