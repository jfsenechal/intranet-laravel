<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Minutes\Pages;

use AcMarche\Conseil\Filament\Resources\Minutes\MinuteResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditMinute extends EditRecord
{
    #[Override]
    protected static string $resource = MinuteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
