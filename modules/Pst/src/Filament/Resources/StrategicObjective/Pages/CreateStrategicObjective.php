<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\StrategicObjective\Pages;

use AcMarche\Pst\Filament\Resources\StrategicObjective\StrategicObjectiveResource;
use AcMarche\Security\Repository\UserRepository;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateStrategicObjective extends CreateRecord
{
    #[Override]
    protected static string $resource = StrategicObjectiveResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['department'] = UserRepository::departmentSelected();

        return $data;
    }
}
