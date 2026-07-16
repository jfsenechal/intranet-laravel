<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Employes\Pages;

use AcMarche\EmailManagement\Filament\Actions\SyncFromLdapAction;
use AcMarche\EmailManagement\Filament\Resources\Employes\EmployeResource;
use AcMarche\EmailManagement\Filament\Resources\Employes\Tables\EmployesTable;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

final class ListEmployes extends ListRecords
{
    protected static string $resource = EmployeResource::class;

    protected static ?string $navigationLabel = 'Employés';

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' employés';
    }

    public function table(Table $table): Table
    {
        return EmployesTable::configure($table);
    }

    protected function getHeaderActions(): array
    {
        return [
            SyncFromLdapAction::make(),
        ];
    }
}
