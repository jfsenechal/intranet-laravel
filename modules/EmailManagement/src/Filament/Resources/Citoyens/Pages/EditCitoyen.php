<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Citoyens\Pages;

use AcMarche\EmailManagement\Filament\Resources\Citoyens\CitoyenResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

final class EditCitoyen extends EditRecord
{
    protected static string $resource = CitoyenResource::class;

    public function getTitle(): string
    {
        return $this->record->mail;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon(Heroicon::Eye),
        ];
    }
}
