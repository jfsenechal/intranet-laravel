<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\ContractTypes\Pages;

use AcMarche\Hrm\Filament\Resources\ContractTypes\ContractTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class ViewContractType extends ViewRecord
{
    #[Override]
    protected static string $resource = ContractTypeResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon(Heroicon::Pencil),
            DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
