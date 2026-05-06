<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Clients\Pages;

use AcMarche\MealDelivery\Filament\Resources\Clients\ClientResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditClient extends EditRecord
{
    #[Override]
    protected static string $resource = ClientResource::class;

    public function getTitle(): string
    {
        return $this->record->last_name.' '.$this->record->first_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
