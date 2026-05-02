<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\Orders\Pages;

use AcMarche\CpasRepas\Filament\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListOrders extends ListRecords
{
    #[Override]
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return 'Orders';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add order')
                ->icon('tabler-plus'),
        ];
    }
}
