<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Users\Pages;

use AcMarche\Mileage\Filament\Resources\Users\UserResource;
use AcMarche\Mileage\Handler\UserHandler;
use App\Models\User;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

final class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Ajouter un agent';
    }

    /**
     * Create PersonalInformation after creating/enrolling a user in the mileage system
     */
    protected function afterCreate(): void
    {
        /** @var User $user */
        $user = $this->record;

        try {
            UserHandler::createPersonalInformation($user, $this->data);
        } catch (Exception $e) {
            Notification::make()
                ->warning()
                ->title('Les données personnelles sont manquantes')
                ->body($e->getMessage())
                ->persistent()
                ->send();
        }
    }
}
