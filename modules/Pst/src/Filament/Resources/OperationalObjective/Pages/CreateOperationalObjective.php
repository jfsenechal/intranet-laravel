<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\OperationalObjective\Pages;

use AcMarche\Pst\Filament\Resources\OperationalObjective\OperationalObjectiveResource;
use AcMarche\Security\Repository\UserRepository;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateOperationalObjective extends CreateRecord
{
    #[Override]
    protected static string $resource = OperationalObjectiveResource::class;

    #[Override]
    protected static bool $canCreateAnother = false;

    public function getTitle(): string
    {
        return 'Nouvel objectif Opérationnel (Oo)';
    }

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
