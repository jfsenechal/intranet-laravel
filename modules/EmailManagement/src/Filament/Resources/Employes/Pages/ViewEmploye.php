<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Employes\Pages;

use AcMarche\EmailManagement\Filament\Actions\AliasAction;
use AcMarche\EmailManagement\Filament\Actions\CreateEmailAction;
use AcMarche\EmailManagement\Filament\Actions\QuotaAction;
use AcMarche\EmailManagement\Filament\Actions\VacationAction;
use AcMarche\EmailManagement\Filament\Actions\ViewLdapAction;
use AcMarche\EmailManagement\Filament\Resources\Employes\EmployeResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;

final class ViewEmploye extends ViewRecord
{
    protected static string $resource = EmployeResource::class;

    public function getTitle(): string
    {
        return $this->record->mail ?? 'Sans email';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewLdapAction::make($this->record),
            EditAction::make()
                ->icon(Heroicon::Pencil),
            QuotaAction::make($this->record),
            ActionGroup::make([
                CreateEmailAction::make($this->record),
                AliasAction::make($this->record),
                VacationAction::make($this->record),
            ])
                ->label('Actions...')
                ->button()
                ->size(Size::Large)
                ->color('secondary'),
        ];
    }
}
