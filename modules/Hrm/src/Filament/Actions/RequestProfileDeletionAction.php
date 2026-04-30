<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Actions;

use AcMarche\Agent\Mail\ProfileDeleteRequestMail;
use AcMarche\Hrm\Models\Employee;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;

final class RequestProfileDeletionAction
{
    public static function make(): Action
    {
        return Action::make('requestProfileDeletion')
            ->label('Demander la suppression')
            ->icon(Heroicon::OutlinedTrash)
            ->iconPosition(IconPosition::After)
            ->link()
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(fn (Employee $record): string => 'Suppression du compte de '.mb_strtoupper((string) $record->last_name).' '.$record->first_name)
            ->modalDescription('Un e-mail sera envoyé au service informatique pour demander la suppression du profil.')
            ->action(function (Employee $record): void {
                $to = config('agent.informatique_email');
                if (empty($to)) {
                    Notification::make()
                        ->title('Adresse informatique non configurée')
                        ->danger()
                        ->send();

                    return;
                }

                Mail::to($to)->send(new ProfileDeleteRequestMail($record));

                Notification::make()
                    ->title('Demande envoyée au service informatique')
                    ->success()
                    ->send();
            });
    }
}
