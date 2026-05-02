<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\Diets\Pages;

use AcMarche\CpasRepas\Filament\Resources\Diets\DietResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditDiet extends EditRecord
{
    #[Override]
    protected static string $resource = DietResource::class;

    public function getTitle(): string
    {
        return 'Edit diet';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
