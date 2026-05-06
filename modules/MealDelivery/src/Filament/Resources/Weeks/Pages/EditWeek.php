<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks\Pages;

use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditWeek extends EditRecord
{
    #[Override]
    protected static string $resource = WeekResource::class;

    public function getTitle(): string
    {
        return 'Semaine du '.$this->record->first_day;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
