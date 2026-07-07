<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Notifications\Pages;

use AcMarche\College\Enums\NotificationType;
use AcMarche\College\Filament\Resources\Notifications\Schemas\NotificationSendForm;
use AcMarche\College\Mail\NotificationMail;
use AcMarche\College\Models\Notification;
use AcMarche\College\Models\Recipient;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Override;

final class CreateNotification extends Page implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed> */
    public array $data = [];

    #[Override]
    protected string $view = 'college::filament.pages.create-notification';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;

    #[Override]
    protected static ?int $navigationSort = 2;

    #[Override]
    protected static ?string $navigationLabel = 'Notifier';

    public static function canAccess(array $parameters = []): bool
    {
        return Gate::check('create', Notification::class);
    }

    public function getTitle(): string
    {
        return 'Envoyer une notification';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return NotificationSendForm::configure($schema->statePath('data'));
    }

    public function send(): void
    {
        $data = $this->form->getState();

        $type = $data['type'] instanceof NotificationType
            ? $data['type']
            : NotificationType::from((string) $data['type']);

        $files = $this->collectFiles($data);

        $count = $this->sendNotifications($type, $data, $files);

        FilamentNotification::make()
            ->title('Notification envoyée')
            ->body(sprintf('%d destinataire(s) notifié(s).', $count))
            ->success()
            ->send();
    }

    /**
     * @return array<int, Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('send')
                ->label('Envoyer la notification')
                ->icon(Heroicon::PaperAirplane)
                ->submit('send'),
        ];
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
     * @return int Number of recipients actually notified.
     */
    private function sendNotifications(NotificationType $type, array $data, array $files): int
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
        $count = 0;

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

            $count++;
        }

        return $count;
    }
}
