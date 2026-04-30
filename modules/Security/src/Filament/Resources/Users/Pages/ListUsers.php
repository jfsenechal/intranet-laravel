<?php

declare(strict_types=1);

namespace AcMarche\Security\Filament\Resources\Users\Pages;

use AcMarche\Security\Filament\Resources\Users\Schemas\UserForm;
use AcMarche\Security\Filament\Resources\Users\UserResource;
use AcMarche\Security\Ldap\UserHandler;
use App\Models\User;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Override;

final class ListUsers extends ListRecords
{
    #[Override]
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' agents';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ImportUser')
                ->label('Importer un agent')
                ->icon('tabler-user-plus')
                ->modal()
                ->modalHeading('Importer un agent de la LDAP')
                ->schema(fn (Schema $schema): Schema => UserForm::add($schema))
                ->action(function (array $data): void {
                    try {
                        $user = UserHandler::createUserFromLdap($data);
                        Notification::make()
                            ->success()
                            ->title('Utilisateur ajouté')
                            ->send();
                        if ($user instanceof User) {
                            $this->redirect(UserResource::getUrl('view', ['record' => $user], panel: 'security-panel'));
                        }
                    } catch (Exception $exception) {
                        Notification::make()
                            ->danger()
                            ->title($exception->getMessage())
                            ->send();
                    }
                }),
        ];
    }
}
