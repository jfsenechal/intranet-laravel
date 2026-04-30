<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\ContractTypes\Pages;

use AcMarche\Hrm\Filament\Resources\ContractTypes\ContractTypeResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditContractType extends EditRecord
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
            ViewAction::make()
                ->icon(Heroicon::Eye),
        ];
    }
}
