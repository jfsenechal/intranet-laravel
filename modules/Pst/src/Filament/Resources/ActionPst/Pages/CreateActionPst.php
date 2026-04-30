<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\ActionPst\Pages;

use AcMarche\Pst\Events\ActionProcessed;
use AcMarche\Pst\Filament\Resources\ActionPst\ActionPstResource;
use AcMarche\Security\Repository\UserRepository;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

final class CreateActionPst extends CreateRecord
{
    #[Override]
    protected static string $resource = ActionPstResource::class;

    #[Override]
    protected static ?string $title = 'Ajouter une action';

    /**
     * to set department before save
     */
    protected function handleRecordCreation(array $data): Model
    {
        $record = new ($this->getModel())($data);
        if (
            self::getResource()::isScopedToTenant() &&
            ($tenant = Filament::getTenant())
        ) {
            return $this->associateRecordWithTenant($record, $tenant);
        }

        $record->department = UserRepository::departmentSelected();
        $record->save();

        return $record;
    }

    protected function afterCreate(): void
    {
        if ($this->record->validated === false) {
            event(new ActionProcessed($this->record));
        }
    }
}
