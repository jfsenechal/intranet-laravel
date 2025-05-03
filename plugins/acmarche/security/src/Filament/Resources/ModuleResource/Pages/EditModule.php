<?php

namespace AcMarche\Security\Filament\Resources\ModuleResource\Pages;

use AcMarche\Security\Filament\Resources\ModuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditModule extends EditRecord
{
    protected static string $resource = ModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->name;
    }
}
