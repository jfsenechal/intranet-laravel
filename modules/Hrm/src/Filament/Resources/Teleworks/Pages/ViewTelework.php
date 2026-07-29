<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Teleworks\Pages;

use AcMarche\Hrm\Filament\Resources\Teleworks\TeleworkResource;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Telework;
use AcMarche\Hrm\Services\TeleworkNotifier;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Gate;
use Override;

final class ViewTelework extends ViewRecord
{
    #[Override]
    protected static string $resource = TeleworkResource::class;

    private ?Employee $resolvedDirector = null;

    private bool $hasResolvedDirector = false;

    public function getTitle(): string|Htmlable
    {
        return 'Télétravail - '.$this->record->user_add;
    }

    public function requestManagerValidation(): void
    {
        /** @var Telework $telework */
        $telework = $this->record;
        $director = $this->director();

        if (! TeleworkNotifier::notifyManagerOfNewRequest($telework)) {
            Notification::make()
                ->title('Aucun mail envoyé')
                ->body('Aucun directeur avec une adresse email n\'a pu être déterminé pour cet agent.')
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Demande de validation envoyée')
            ->body(sprintf('Un mail avec le lien de validation a été envoyé à %s.', $director?->full_name))
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('requestManagerValidation')
                ->label('Demander la validation')
                ->icon(Heroicon::PaperAirplane)
                ->color('info')
                ->visible(fn (): bool => Gate::forUser(auth()->user())->check('hrm-administrator')
                    && $this->record->manager_validated === null)
                ->disabled(fn (): bool => ! $this->hasNotifiableDirector())
                ->tooltip(fn (): ?string => $this->hasNotifiableDirector()
                    ? null
                    : 'Aucun directeur avec une adresse email n\'a pu être déterminé pour cet agent')
                ->requiresConfirmation()
                ->modalHeading('Envoyer la demande de validation')
                ->modalDescription(fn (): string => sprintf(
                    'Un mail contenant le lien de validation sera envoyé à %s (%s).',
                    $this->director()?->full_name,
                    $this->director()?->professional_email,
                ))
                ->modalSubmitActionLabel('Envoyer')
                ->action(fn () => $this->requestManagerValidation()),
            Action::make('managerValidate')
                ->label('Validation du directeur')
                ->icon(Heroicon::CheckBadge)
                ->url(fn () => ManagerValidateTelework::getUrl(['record' => $this->record]))
                ->visible(fn (): bool => ManagerValidateTelework::canAccess(['record' => $this->record])),
            Action::make('hrValidate')
                ->label('Traitement GRH')
                ->icon(Heroicon::ClipboardDocumentCheck)
                ->url(fn () => HrValidateTelework::getUrl(['record' => $this->record]))
                ->visible(fn (): bool => HrValidateTelework::canAccess(['record' => $this->record])),
            EditAction::make()
                ->icon(Heroicon::Pencil),
            DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }

    /**
     * Whether a director with an email address is resolvable, i.e. whether the
     * validation request can actually be delivered.
     */
    private function hasNotifiableDirector(): bool
    {
        $director = $this->director();

        return $director instanceof Employee && filled($director->professional_email);
    }

    /**
     * The director the validation request is addressed to, resolved once per request.
     */
    private function director(): ?Employee
    {
        if (! $this->hasResolvedDirector) {
            $employee = $this->record->employee;

            $this->resolvedDirector = $employee instanceof Employee
                ? TeleworkNotifier::director($employee)
                : null;
            $this->hasResolvedDirector = true;
        }

        return $this->resolvedDirector;
    }
}
