<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\Orders\Pages;

use AcMarche\CpasRepas\Filament\Resources\Orders\OrderResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditOrder extends EditRecord
{
    #[Override]
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return 'Edit order';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
