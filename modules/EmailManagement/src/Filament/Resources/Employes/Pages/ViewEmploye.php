<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Employes\Pages;

use AcMarche\EmailManagement\Filament\Actions\DeleteEmployeAction;
use AcMarche\EmailManagement\Filament\Actions\PasswordLdapAction;
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
    {        return [
            ActionGroup::make([
                ViewLdapAction::make($this->record),
                PasswordLdapAction::make($this->record),
                EditAction::make()
                    ->label('Modifier')
                    ->icon(Heroicon::Pencil),
                DeleteEmployeAction::make($this->record),
            ])
                ->label('Actions...')
                ->button()
                ->size(Size::Large)
                ->color('secondary'),
        ];
    }
}
