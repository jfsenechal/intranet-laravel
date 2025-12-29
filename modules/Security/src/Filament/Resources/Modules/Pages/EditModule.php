<?php

namespace AcMarche\Security\Filament\Resources\Modules\Pages;

use AcMarche\Security\Filament\Resources\Modules\ModuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

final class EditModule extends EditRecord
{
    protected static string $resource = ModuleResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
