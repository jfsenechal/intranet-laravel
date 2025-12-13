<?php

namespace AcMarche\Courrier\Filament\Resources\RecipientResource\Pages;

use AcMarche\Courrier\Filament\Resources\RecipientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

final class EditRecipient extends EditRecord
{
    protected static string $resource = RecipientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->last_name.' '.$this->getRecord()->first_name;
    }
}
