<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Notifications\Pages;

use AcMarche\College\Enums\NotificationType;
use AcMarche\College\Filament\Resources\Notifications\NotificationResource;
use AcMarche\College\Filament\Resources\Notifications\Schemas\NotificationSendForm;
use AcMarche\College\Mail\NotificationMail;
use AcMarche\College\Models\Notification;
use AcMarche\College\Models\Recipient;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Override;

final class CreateNotification extends CreateRecord
{
    #[Override]
    protected static string $resource = NotificationResource::class;

    public function getTitle(): string
    {
        return 'Envoyer une notification';
    }

    #[Override]
    public function form(Schema $schema): Schema
    {
        return NotificationSendForm::configure($schema);
    }

    #[Override]
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    #[Override]
    protected function getCreatedNotificationTitle(): string
    {
        return 'Notification envoyée aux destinataires';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    #[Override]
    protected function handleRecordCreation(array $data): Model
    {
        $type = $data['type'] instanceof NotificationType
            ? $data['type']
            : NotificationType::from((string) $data['type']);

        $files = $this->collectFiles($data);

        $notifications = collect($files)->map(
            fn (array $file): Notification => Notification::create([
                'file_name' => $file['name'],
                'mime' => $file['mime'],
                'updatedAt' => now(),
            ]),
        );

        $this->sendNotifications($type, $data, $files);

        return $notifications->first();
    }

    /**
     * Resolve the uploaded documents into attachment metadata.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, array{path: string, name: string, mime: string}>
     */
    private function collectFiles(array $data): array
    {
        $disk = Storage::disk('local');
        $files = [];

        foreach (['college', 'service'] as $scope) {
            $path = $data["file_{$scope}"] ?? null;

            if (blank($path)) {
                continue;
            }

            $files[$scope] = [
                'path' => (string) $path,
                'name' => basename((string) $path),
                'mime' => $disk->mimeType($path) ?: 'application/octet-stream',
            ];
        }

        return $files;
    }

    /**
     * Email every recipient flagged for this notification type, attaching only
     * the document(s) matching their checked boxes.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, array{path: string, name: string, mime: string}>  $files
     */
    private function sendNotifications(NotificationType $type, array $data, array $files): void
    {
        $recipients = Recipient::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where(function (Builder $query) use ($type): void {
                $query->where($type->collegeColumn(), true)
                    ->orWhere($type->serviceColumn(), true);
            })
            ->get();

        $dateCollege = Carbon::parse($data['date_college']);

        $sender = auth()->user();

        foreach ($recipients as $recipient) {
            $attachments = [];

            if ($recipient->{$type->collegeColumn()} && isset($files['college'])) {
                $attachments[] = $files['college'];
            }

            if ($recipient->{$type->serviceColumn()} && isset($files['service'])) {
                $attachments[] = $files['service'];
            }

            if ($attachments === []) {
                continue;
            }

            Mail::to($recipient->email)->queue(new NotificationMail(
                sujet: $data['sujet'],
                body: $data['message'],
                dateCollege: $dateCollege,
                files: $attachments,
                fromAddress: $sender->email,
                fromName: $sender->full_name,
            ));
        }
    }
}
