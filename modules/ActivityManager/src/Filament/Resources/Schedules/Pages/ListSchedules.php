<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Schedules\Pages;

use AcMarche\ActivityManager\Filament\Resources\Schedules\SchedulesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListSchedules extends ListRecords
{
    #[Override]
    protected static string $resource = SchedulesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau cours')
                ->icon(Heroicon::Plus),
        ];
    }
}
