<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Actions;

use AcMarche\EmailManagement\Filament\Resources\Citoyens\Schemas\PasswordInput;
use AcMarche\EmailManagement\Models\Citoyen;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

final class HandAction
{
    public static function takeOver(Model|Citoyen $record): Action
    {
        return Action::make('hand')
            ->label('Prendre la main')
            ->icon(Heroicon::HandRaised)
            ->color('danger')
            ->authorize('update', $record)
            ->visible(fn (): bool => ! $record->isTakenOver())
            ->modalHeading("Prendre la main sur {$record->mail}")
            ->modalDescription('Le mot de passe du citoyen sera remplacé par un mot de passe temporaire, puis pourra être restauré.')
            ->modalSubmitActionLabel('Prendre la main')
            ->schema([
                PasswordInput::make('Mot de passe temporaire'),
            ])
            ->action(function (array $data) use ($record): void {
                $record->takeOver($data['password']);

                Notification::make()
                    ->title('Mot de passe temporaire défini')
                    ->success()
                    ->send();
            });
    }

    public static function restore(Model|Citoyen $record): Action
    {
        return Action::make('restoreHand')
            ->label('Restaurer le mot de passe')
            ->icon(Heroicon::ArrowUturnLeft)
            ->color('success')
            ->authorize('update', $record)
            ->visible(fn (): bool => $record->isTakenOver())
            ->requiresConfirmation()
            ->modalHeading('Restaurer le mot de passe original')
            ->modalDescription('Le mot de passe original du citoyen sera restauré et le mot de passe temporaire ne fonctionnera plus.')
            ->action(function () use ($record): void {
                $record->restorePassword();

                Notification::make()
                    ->title('Mot de passe original restauré')
                    ->success()
                    ->send();
            });
    }
}
