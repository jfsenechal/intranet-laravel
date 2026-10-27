<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Citoyens\Pages;

use AcMarche\EmailManagement\Filament\Resources\Citoyens\CitoyenResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

final class ViewCitoyen extends ViewRecord
{
    protected static string $resource = CitoyenResource::class;

    public function getTitle(): string
    {
        return $this->record->mail;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon(Heroicon::Pencil),
        ];
    }
}
