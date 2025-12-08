<?php

namespace AcMarche\Security\Filament\Resources\UserResource\Pages;

use AcMarche\Security\Filament\Resources\UserResource;
use AcMarche\Security\Filament\Resources\UserResource\Schema\UserForm;
use AcMarche\Security\Ldap\UserHandler;
use Exception;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

final class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' agents';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('ImportUser')
                ->label('Importer un agent')
                ->icon('tabler-user-plus')
                ->modal()
                ->modalHeading('Importer un agent de la LDAP')
                ->schema(fn (Schema $schema) => UserForm::add($schema))
                ->action(function (array $data) {
                    try {
                        $user = UserHandler::createUserFromLdap($data);
                        Notification::make()
                            ->success()
                            ->title('Utilisateur ajouté')
                            ->send();
                        if ($user) {
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
