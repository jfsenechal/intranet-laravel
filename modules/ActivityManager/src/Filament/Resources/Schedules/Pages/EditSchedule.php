<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Schedules\Pages;

use AcMarche\ActivityManager\Filament\Resources\Schedules\SchedulesResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditSchedule extends EditRecord
{
    #[Override]
    protected static string $resource = SchedulesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
