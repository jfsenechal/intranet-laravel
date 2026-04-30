<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Actions;

use AcMarche\Agent\Mail\ProfileChangeRequestMail;
use AcMarche\Hrm\Models\Employee;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;

final class RequestProfileChangeAction
{
    public static function make(): Action
    {
        return Action::make('requestProfileChange')
            ->label('Demander un changement')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->iconPosition(IconPosition::After)
            ->link()
            ->color('primary')
            ->visible(fn (Employee $record): bool => $record->profile !== null)
            ->modalHeading(fn (Employee $record): string => 'Changement pour le compte de '.mb_strtoupper((string) $record->last_name).' '.$record->first_name)
            ->modalDescription('Prévenez le service informatique et le chef de service d\'un changement pour cet employé.')
            ->schema([
                Textarea::make('notes')
                    ->label('Remarques')
                    ->placeholder('Par exemple, changement de service, mise à la retraite...')
                    ->required()
                    ->rows(5),
            ])
            ->action(function (array $data, Employee $record): void {
                $to = config('agent.informatique_email');
                if (empty($to)) {
                    Notification::make()
                        ->title('Adresse informatique non configurée')
                        ->danger()
                        ->send();

                    return;
                }

                Mail::to($to)->send(new ProfileChangeRequestMail($record, $data['notes']));

                Notification::make()
                    ->title('Demande envoyée au service informatique')
                    ->success()
                    ->send();
            });
    }
}
