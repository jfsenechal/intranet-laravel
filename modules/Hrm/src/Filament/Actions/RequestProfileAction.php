<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Actions;

use AcMarche\Agent\Mail\ProfileRequestMail;
use AcMarche\Hrm\Models\Employee;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;

final class RequestProfileAction
{
    public static function make(): Action
    {
        return Action::make('requestProfile')
            ->label('Demander un compte informatique')
            ->icon(Heroicon::OutlinedEnvelope)
            ->iconPosition(IconPosition::After)
            ->link()
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Demander un compte informatique')
            ->modalDescription('Un e-mail sera envoyé au service informatique.')
            ->action(function (Employee $record): void {
                $to = config('agent.informatique_email');
                if (empty($to)) {
                    Notification::make()
                        ->title('Adresse informatique non configurée')
                        ->danger()
                        ->send();

                    return;
                }

                Mail::to($to)->send(new ProfileRequestMail($record));

                Notification::make()
                    ->title('Demande envoyée au service informatique')
                    ->success()
                    ->send();
            });
    }
}
