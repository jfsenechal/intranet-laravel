<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Clients\Pages;

use AcMarche\MealDelivery\Filament\Resources\Clients\ClientResource;
use AcMarche\MealDelivery\Filament\Resources\Clients\Schemas\ClientInfoList;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Override;

final class ViewClient extends ViewRecord
{
    #[Override]
    protected static string $resource = ClientResource::class;

    public function getTitle(): string
    {
        return $this->record->last_name.' '.$this->record->first_name;
    }

    public function infolist(Schema $schema): Schema
    {
        return ClientInfoList::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon('tabler-edit'),
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
