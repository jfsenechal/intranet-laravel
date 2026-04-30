<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Actions;

use AcMarche\MailingList\Enums\EmailStatus;
use AcMarche\MailingList\Handler\MailerHandler;
use AcMarche\MailingList\Models\Email;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

final class SendAction
{
    public const int MAX_ATTACHMENTS_SIZE_MB = 20;

    public static function make(Email|Model $email): Action
    {
        return Action::make('send')
            ->label('Envoyer')
            ->icon(Heroicon::PaperAirplane)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Envoyer la newsletter')
            ->modalDescription(
                fn (): string => "Cet e-mail sera envoyé à {$email->total_count} destinataires. Continuer ?"
            )
            ->disabled(fn (): bool => $email->total_count < 1 || self::attachmentsSizeExceeded($email))
            ->visible(
                fn (
                ): bool => $email->status === EmailStatus::Draft || $email->status === EmailStatus::Failed
            )
            ->action(fn () => MailerHandler::sendEmail($email));
    }

    public static function attachmentsSizeMb(Email|Model $email): float
    {
        $disk = Storage::disk('public');

        $totalBytes = collect($email->attachments ?? [])
            ->sum(fn (string $path): int => $disk->size($path));

        return round($totalBytes / 1024 / 1024, 2);
    }

    public static function attachmentsSizeExceeded(Email|Model $email): bool
    {
        return self::attachmentsSizeMb($email) > self::MAX_ATTACHMENTS_SIZE_MB;
    }
}
