<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks\Pages;

use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use AcMarche\MealDelivery\Service\WeekDaysBuilder;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateWeek extends CreateRecord
{
    #[Override]
    protected static string $resource = WeekResource::class;

    public function getTitle(): string
    {
        return 'Ajouter une semaine';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['days']) && ! empty($data['first_day'])) {
            $data['days'] = (new WeekDaysBuilder())->fullWeek($data['first_day']);
        }

        return $data;
    }
}
