<?php

declare(strict_types=1);

namespace AcMarche\Ad\Filament\Pages;

use AcMarche\Ad\Models\Subscriber;
use AcMarche\Ad\Services\SubscriptionException;
use AcMarche\Ad\Services\SubscriptionService;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class SubscriptionPage extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $subscribeData = [];

    public ?array $unsubscribeData = [];

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = Heroicon::Bell;

    #[Override]
    protected static ?string $navigationLabel = 'Abonnement aux annonces';

    #[Override]
    protected static ?int $navigationSort = 50;

    #[Override]
    protected string $view = 'ad::filament.pages.subscription';

    public function getTitle(): string
    {
        return "S'abonner aux nouvelles annonces";
    }

    /**
     * @return array<int, string>
     */
    protected function getForms(): array
    {
        return ['subscribeForm', 'unsubscribeForm'];
    }

    public function subscribeForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Votre adresse email')
                    ->email()
                    ->required()
                    ->maxLength(255),
            ])
            ->statePath('subscribeData');
    }

    public function unsubscribeForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Email à désabonner')
                    ->email()
                    ->required()
                    ->maxLength(255),
            ])
            ->statePath('unsubscribeData');
    }

    public function subscribe(SubscriptionService $service): void
    {
        $email = (string) ($this->subscribeForm->getState()['email'] ?? '');

        try {
            $subscriber = $service->subscribe($email);
        } catch (SubscriptionException $subscriptionException) {
            Notification::make()
                ->title('Abonnement refusé')
                ->body($subscriptionException->getMessage())
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        $this->subscribeForm->fill();

        Notification::make()
            ->title('Abonnement enregistré')
            ->body(sprintf(
                'Merci %s %s, vous recevrez les nouvelles annonces.',
                $subscriber->first_name,
                $subscriber->last_name,
            ))
            ->success()
            ->send();
    }

    public function unsubscribe(SubscriptionService $service): void
    {
        $email = (string) ($this->unsubscribeForm->getState()['email'] ?? '');

        $removed = $service->unsubscribe($email);

        $this->unsubscribeForm->fill();

        if (! $removed) {
            Notification::make()
                ->title('Aucun abonnement trouvé')
                ->body('Cette adresse email n\'est pas abonnée.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('Désabonnement effectué')
            ->success()
            ->send();
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function getViewData(): array
    {
        return [
            'subscribers' => Subscriber::query()->orderBy('last_name')->orderBy('first_name')->get(),
        ];
    }
}
