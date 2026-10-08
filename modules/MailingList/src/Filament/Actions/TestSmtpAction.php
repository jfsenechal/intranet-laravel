<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Actions;

use AcMarche\MailingList\Models\Sender;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Message;
use Throwable;

final class TestSmtpAction
{
    public static function make(): Action
    {
        return Action::make('testSmtp')
            ->label('Tester la configuration')
            ->icon(Heroicon::PaperAirplane)
            ->color('info')
            ->modalHeading('Tester la configuration SMTP')
            ->modalSubmitActionLabel('Envoyer le test')
            ->schema([
                TextInput::make('email')
                    ->label('Adresse e-mail de test')
                    ->email()
                    ->required()
                    ->default(fn (): ?string => auth()->user()?->email),
            ])
            ->action(function (array $data, Sender $record): void {
                $log = self::sendTestEmail($record, $data['email']);

                $notification = Notification::make()
                    ->title($log['success'] ? 'Test SMTP réussi' : 'Échec du test SMTP')
                    ->body(nl2br(e(implode(PHP_EOL, $log['lines']))))
                    ->persistent();

                $log['success']
                    ? $notification->success()
                    : $notification->danger();

                $notification->send();
            });
    }

    /**
     * Send a test e-mail through the sender's resolved mailer and collect a
     * human-readable log of the attempt, including the SMTP dialogue when
     * available.
     *
     * @return array{success: bool, lines: list<string>}
     */
    private static function sendTestEmail(Sender $sender, string $to): array
    {
        $lines = [];

        if ($sender->hasSmtpSettings()) {
            $lines[] = "Mailer : SMTP du sender ({$sender->smtp_host}:".($sender->smtp_port ?? 587).')';
        } else {
            $lines[] = 'Mailer : configuration globale ('.config('mail.default').')';
        }

        $lines[] = "Destinataire : {$to}";
        $lines[] = 'Connexion et envoi en cours…';

        try {
            $mailer = $sender->resolveMailer();

            $mailer->raw(
                'Ceci est un e-mail de test envoyé pour vérifier la configuration SMTP.',
                function (Message $message) use ($to, $sender): void {
                    $message->to($to)
                        ->subject('Test de configuration SMTP')
                        ->from($sender->email, $sender->name);
                },
            );

            if ($mailer instanceof Mailer) {
                $transport = $mailer->getSymfonyTransport();

                if (method_exists($transport, 'getDebug') && filled($debug = $transport->getDebug())) {
                    $lines[] = '--- Dialogue SMTP ---';
                    $lines[] = mb_trim($debug);
                }
            }

            $lines[] = 'Résultat : e-mail envoyé avec succès.';

            return ['success' => true, 'lines' => $lines];
        } catch (Throwable $e) {
            $lines[] = 'Erreur : '.$e->getMessage();

            return ['success' => false, 'lines' => $lines];
        }
    }
}
