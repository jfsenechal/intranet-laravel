<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Recipient\Pages;

use AcMarche\AldermenAgenda\Filament\Resources\Recipient\RecipientResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditRecipient extends EditRecord
{
    #[Override]
    protected static string $resource = RecipientResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->last_name.' '.$this->getRecord()->first_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon(Heroicon::Eye),
        ];
    }
}
