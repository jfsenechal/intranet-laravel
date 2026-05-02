<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\Clients\Pages;

use AcMarche\CpasRepas\Filament\Resources\Clients\ClientResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditClient extends EditRecord
{
    #[Override]
    protected static string $resource = ClientResource::class;

    public function getTitle(): string
    {
        return 'Edit client';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
