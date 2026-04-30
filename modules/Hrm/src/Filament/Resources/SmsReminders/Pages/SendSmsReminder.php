<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\SmsReminders\Pages;

use AcMarche\App\Sms\Exception\SmsException;
use AcMarche\App\Sms\InforiusClient;
use AcMarche\Hrm\Filament\Resources\SmsReminders\Schemas\SmsReminderForm;
use AcMarche\Hrm\Filament\Resources\SmsReminders\SmsReminderResource;
use AcMarche\Hrm\Models\SmsReminder;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;
use Throwable;

final class SendSmsReminder extends Page implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed> */
    public array $data = [];

    #[Override]
    protected static string $resource = SmsReminderResource::class;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = Heroicon::PaperAirplane;

    #[Override]
    protected string $view = 'hrm::filament.sms-reminders.send';

    public function mount(SmsReminder $record): void
    {
        $this->form->fill([
            'phone_number' => $record->phone_number,
            'message' => $record->message,
        ]);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Envoyer un SMS ';
    }

    public function form(Schema $schema): Schema
    {
        return SmsReminderForm::forSending($schema)
            ->statePath('data');
    }

    public function sendAction(): Action
    {
        return Action::make('send')
            ->label('Envoyer le SMS')
            ->icon(Heroicon::PaperAirplane)
            ->color('primary')
            ->requiresConfirmation()
            ->action(fn () => $this->send());
    }

    public function cancelAction(): Action
    {
        return Action::make('cancel')
            ->label('Annuler')
            ->color('gray')
            ->url(SmsReminderResource::getUrl('index'));
    }

    public function send(): void
    {
        $data = $this->form->getState();
        $message = mb_trim(strip_tags((string) $data['message']));

        if ($message === '' || ($data['phone_number'] ?? '') === '') {
            Notification::make()
                ->title('Numéro et message obligatoires')
                ->danger()
                ->send();

            return;
        }

        try {
            $response = InforiusClient::fromConfig()->sendSms(
                number: (string) $data['phone_number'],
                message: $message,
                customerReference: 'reminder-'.(string) $data['phone_number'],
            );
        } catch (SmsException|Throwable $exception) {
            Notification::make()
                ->title('Échec de l\'envoi du SMS')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        $status = $response->messages[0] ?? null;
        $result = $response->isSuccessful()
            ? 'OK'
            : ($response->error ?? $status?->errorMessage ?? 'Erreur inconnue');

        if ($response->isSuccessful()) {
            Notification::make()
                ->title('SMS envoyé')
                ->body(sprintf('Solde restant : %.2f €', $response->balance))
                ->success()
                ->send();

            $this->redirect(SmsReminderResource::getUrl('index'));

            return;
        }

        Notification::make()
            ->title('SMS non envoyé')
            ->body($result)
            ->danger()
            ->send();
    }
}
