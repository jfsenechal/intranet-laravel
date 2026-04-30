<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Directions\Pages;

use AcMarche\Hrm\Filament\Resources\Directions\DirectionResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditDirection extends EditRecord
{
    #[Override]
    protected static string $resource = DirectionResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('heroicon-o-eye'),
        ];
    }
}
