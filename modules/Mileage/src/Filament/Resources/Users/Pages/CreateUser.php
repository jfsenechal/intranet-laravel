<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Users\Pages;

use AcMarche\Mileage\Filament\Resources\Users\UserResource;
use AcMarche\Mileage\Providers\MileageServiceProvider;
use AcMarche\Mileage\Service\PersonalInformationService;
use AcMarche\Security\Handler\ModuleHandler;
use App\Models\User;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

final class CreateUser extends CreateRecord
{
    #[Override]
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Ajouter un agent';
    }

    /**
     * Find the existing user by username instead of inserting a new row.
     * This page enrolls an existing user into the mileage module.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return User::where('username', $data['username'])->firstOrFail();
    }

    /**
     * Assign module roles and create PersonalInformation after enrolling a user.
     */
    protected function afterCreate(): void
    {
        /** @var User $user */
        $user = $this->record;

        try {
            ModuleHandler::addModuleFromUser($user, MileageServiceProvider::$module_id, $this->data['roles'] ?? []);
            PersonalInformationService::createPersonalInformation($user, $this->data);
        } catch (Exception $e) {
            Notification::make()
                ->warning()
                ->title('Erreur lors de l\'inscription')
                ->body($e->getMessage())
                ->persistent()
                ->send();
        }
    }
}
