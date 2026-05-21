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

    public function getTitle(): string
    {
        return $this->record->last_name.' '.$this->record->first_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
