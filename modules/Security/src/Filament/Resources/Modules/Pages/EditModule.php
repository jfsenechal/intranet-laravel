<?php

declare(strict_types=1);

namespace AcMarche\Security\Filament\Resources\Modules\Pages;

use AcMarche\Security\Filament\Resources\Modules\ModuleResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditModule extends EditRecord
{
    #[Override]
    protected static string $resource = ModuleResource::class;

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
