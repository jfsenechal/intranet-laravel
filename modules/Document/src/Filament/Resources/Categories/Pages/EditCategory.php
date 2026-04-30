<?php

declare(strict_types=1);

namespace AcMarche\Document\Filament\Resources\Categories\Pages;

use AcMarche\Document\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditCategory extends EditRecord
{
    #[Override]
    protected static string $resource = CategoryResource::class;

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
