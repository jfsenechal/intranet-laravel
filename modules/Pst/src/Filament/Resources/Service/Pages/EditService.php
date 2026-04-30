<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Service\Pages;

use AcMarche\Pst\Filament\Resources\Service\ServiceResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditService extends EditRecord
{
    #[Override]
    protected static string $resource = ServiceResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
