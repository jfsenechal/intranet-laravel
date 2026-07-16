<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Actions;

use AcMarche\EmailManagement\Models\Employe;
use AcMarche\EmailManagement\Sieve\SieveEmploye;
use AcMarche\EmailManagement\Sieve\VacationScript;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Throwable;

final class VacationAction
{
    public static function make(Model|Employe $record): Action
    {
        return Action::make('vacation')
            ->label("Message d'absence")
            ->icon(Heroicon::PaperAirplane)
            ->color('info')
            ->modalHeading("Message d'absence de {$record->samaccountname}")
            ->modalSubmitActionLabel('Activer')
            ->fillForm(fn (): array => [
                'subject' => 'Je suis absent',
                'message' => "Je répondrai à vos mails à partir du ...\n\nVous pouvez joindre mes collègues à ...",
                'days' => 1,
            ])
            ->schema([
                TextInput::make('subject')
                    ->label('Sujet')
                    ->required()
                    ->maxLength(255),
                Textarea::make('message')
                    ->label('Message')
                    ->required()
                    ->rows(5),
                TextInput::make('days')
                    ->label('Intervalle')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->default(1)
                    ->suffix('jour(s)')
                    ->helperText('Délai avant qu\'un même expéditeur reçoive à nouveau la réponse.'),
            ])
            ->extraModalFooterActions([
                Action::make('removeVacation')
                    ->label('Désactiver')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading("Désactiver le message d'absence")
                    ->action(function (SieveEmploye $sieveEmploye) use ($record): void {
                        try {
                            $sieveEmploye->removeVacation($record->samaccountname);

                            Notification::make()
                                ->title("Le message d'absence a bien été désactivé")
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title("Impossible de désactiver le message d'absence")
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->cancelParentActions(),
            ])
            ->action(function (array $data, SieveEmploye $sieveEmploye) use ($record): void {
                $script = VacationScript::build(
                    $data['subject'],
                    $data['message'],
                    (int) $data['days'],
                    array_filter([$record->mail]),
                );

                try {
                    $sieveEmploye->setVacation($record->samaccountname, $script);

                    Notification::make()
                        ->title("Le message d'absence a bien été activé")
                        ->success()
                        ->send();
                } catch (Throwable $e) {
                    // Throwable, not Exception: the ManageSieve client is a separate package,
                    // so an install that has not been composer install'ed surfaces as an Error
                    // rather than an Exception, and would otherwise take the whole page down.
                    Notification::make()
                        ->title("Impossible d'activer le message d'absence")
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
