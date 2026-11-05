<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Employes\Pages;

use AcMarche\EmailManagement\Filament\Actions\AliasAction;
use AcMarche\EmailManagement\Filament\Actions\CreateEmailAction;
use AcMarche\EmailManagement\Filament\Actions\QuotaAction;
use AcMarche\EmailManagement\Filament\Actions\VacationAction;
use AcMarche\EmailManagement\Filament\Resources\Employes\EmployeResource;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

final class ViewEmploye extends ViewRecord
{
    protected static string $resource = EmployeResource::class;

    public function getTitle(): string
    {
        return $this->record->mail ?? $this->record->sn.' '.$this->record->givenName;
    }

    protected function getHeaderActions(): array
    {
        return [
            QuotaAction::make($this->record),
            ActionGroup::make([
                ActionGroup::make([
                    CreateEmailAction::make($this->record),
                    //   AliasAction::make($this->record),
                    VacationAction::make($this->record),
                ])->dropdown(false),
            ])
                ->label('Actions...')
                ->color('success')
                ->icon('tabler-hand-middle-finger')
                ->dropdownWidth(Width::FitContent)
                ->button(),
        ];
    }
}
