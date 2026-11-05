<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Resources\Citoyens\Pages;

use AcMarche\EmailManagement\Filament\Resources\Citoyens\CitoyenResource;
use AcMarche\EmailManagement\Filament\Resources\Citoyens\Schemas\CitoyenForm;
use AcMarche\EmailManagement\Models\Citoyen;
use AcMarche\EmailManagement\Repository\CitoyenRepository;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Js;

final class CreateCitoyen extends CreateRecord
{
    protected static string $resource = CitoyenResource::class;

    public function form(Schema $schema): Schema
    {
        return CitoyenForm::forCreating($schema);
    }

    /**
     * Shows what to send to the citizen. It can only be closed by leaving the page, because
     * the password cannot be read back afterwards and submitting the form again would fail.
     */
    public function credentialsAction(): Action
    {
        return Action::make('credentials')
            ->modalHeading('Le citoyen a bien été ajouté')
            ->modalDescription('Le mot de passe ne sera plus affiché après avoir quitté cette page.')
            ->modalCloseButton(false)
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalCancelAction(false)
            ->modalSubmitActionLabel('Voir la fiche')
            ->fillForm(fn (): array => ['message' => $this->credentialsMessage()])
            ->schema([
                Textarea::make('message')
                    ->hiddenLabel()
                    ->readOnly()
                    ->rows(10),
            ])
            ->extraModalFooterActions([
                Action::make('copyCredentials')
                    ->label('Copier')
                    ->icon(Heroicon::ClipboardDocument)
                    ->color('gray')
                    ->alpineClickHandler(function (): string {
                        $message = Js::from($this->credentialsMessage());
                        $successTitle = Js::from('Texte copié dans le presse-papiers');
                        $failureTitle = Js::from('Impossible de copier le texte');

                        return <<<JS
                            window.navigator.clipboard.writeText({$message})
                                .then(() => new FilamentNotification().title({$successTitle}).success().send())
                                .catch(() => new FilamentNotification().title({$failureTitle}).danger().send())
                            JS;
                    }),
            ])
            ->action(function (): void {
                $this->redirect(CitoyenResource::getUrl('view', ['record' => $this->getRecord()]));
            });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Citoyen
    {
        return app(CitoyenRepository::class)->create($data);
    }

    /**
     * Stays on the page instead of redirecting: the password is still in the form state here,
     * so it never has to go through the session.
     */
    protected function afterCreate(): void
    {
        $this->mountAction('credentials');

        $this->halt();
    }

    private function credentialsMessage(): string
    {
        return implode("\n\n", [
            'Bonjour,',
            "L'adresse **{$this->getRecord()->mail}** a bien été créée.",
            'Vous pouvez la consulter sur https://citoyen.marche.be',
            "Le nom d'utilisateur est : **{$this->getRecord()->uid}**",
            "Le mot de passe est : **{$this->data['password']}**",
            'Vous pouvez configurer votre mail marche.be dans un logiciel de messagerie en vous aidant du tutoriel sur la page :',
            'https://www.marche.be/email/',
            'Votre mot de passe peut être changé sur https://citoyen.marche.be.',
            'Lorsque vous êtes connecté, cliquez sur "Paramètres" puis "Mot de passe"',
            'Bien à vous,',
        ]);
    }
}
