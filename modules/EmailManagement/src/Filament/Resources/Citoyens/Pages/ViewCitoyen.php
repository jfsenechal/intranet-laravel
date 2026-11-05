<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Citoyens\Pages;

use AcMarche\EmailManagement\Filament\Actions\ChangePasswordAction;
use AcMarche\EmailManagement\Filament\Actions\CitoyenQuotaAction;
use AcMarche\EmailManagement\Filament\Actions\DeleteCitoyenAction;
use AcMarche\EmailManagement\Filament\Actions\HandAction;
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
            ChangePasswordAction::make($this->record),
            CitoyenQuotaAction::make($this->record),
            HandAction::takeOver($this->record),
            HandAction::restore($this->record),
            DeleteCitoyenAction::make()
                ->icon(Heroicon::Trash)
                ->successRedirectUrl(CitoyenResource::getUrl('index')),
        ];
    }
}
