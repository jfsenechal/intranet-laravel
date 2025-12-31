<?php

namespace AcMarche\Courrier\Handler;

use AcMarche\Courrier\Exception\ImapException;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Repository\ImapRepository;
use Exception;
use Filament\Notifications\Notification;

final class IncomingMailHandler
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function handleIncomingMailCreation(array $data, string $uid, int $attachmentCount): void
    {
        $imapRepository = new ImapRepository();

        try {
            // Create the incoming mail
            $incomingMail = IncomingMail::create([
                'reference_number' => $data['reference_number'],
                'sender' => $data['sender'],
                'mail_date' => $data['mail_date'],
                'description' => $data['description'] ?? null,
                'is_registered' => $data['is_registered'] ?? false,
                'has_acknowledgment' => $data['has_acknowledgment'] ?? false,
                'is_notified' => false,
            ]);

            // Attach primary services
            if (! empty($data['primary_services'])) {
                foreach ($data['primary_services'] as $serviceId) {
                    $incomingMail->services()->attach($serviceId, ['is_primary' => true]);
                }
            }

            // Attach secondary services
            if (! empty($data['secondary_services'])) {
                foreach ($data['secondary_services'] as $serviceId) {
                    $incomingMail->services()->attach($serviceId, ['is_primary' => false]);
                }
            }

            // Attach primary recipients
            if (! empty($data['primary_recipients'])) {
                foreach ($data['primary_recipients'] as $recipientId) {
                    $incomingMail->recipients()->attach($recipientId, ['is_primary' => true]);
                }
            }

            // Attach secondary recipients
            if (! empty($data['secondary_recipients'])) {
                foreach ($data['secondary_recipients'] as $recipientId) {
                    $incomingMail->recipients()->attach($recipientId, ['is_primary' => false]);
                }
            }

            Notification::make()
                ->title('Courrier créé')
                ->body("Le courrier #{$incomingMail->reference_number} a été créé avec succès.")
                ->success()
                ->send();

            // If the message has only one attachment, delete it
            if ($attachmentCount === 1) {
                try {
                    $imapRepository->deleteMessage($uid);

                    Notification::make()
                        ->title('Message supprimé')
                        ->body('Le message a été supprimé de la boîte mail.')
                        ->success()
                        ->send();
                } catch (ImapException $e) {
                    Notification::make()
                        ->title('Erreur lors de la suppression du message')
                        ->body($e->getMessage())
                        ->warning()
                        ->send();
                }
            }
        } catch (Exception $e) {
            report($e);

            Notification::make()
                ->title('Erreur lors de la création du courrier')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
