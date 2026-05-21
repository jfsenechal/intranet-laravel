<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Pages;

use AcMarche\Conseil\Mail\ConseilNotificationMail;
use AcMarche\Conseil\Models\Attachment;
use AcMarche\Conseil\Models\Group;
use AcMarche\Conseil\Models\Recipient;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Override;

final class NotifyRecipients extends Page implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed> */
    public array $data = [];

    #[Override]
    protected string $view = 'conseil::filament.pages.notify-recipients';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    #[Override]
    protected static ?int $navigationSort = 4;

    #[Override]
    protected static ?string $navigationLabel = 'Notifier les destinataires';

    public function getTitle(): string
    {
        return 'Notifier les destinataires';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Groupe')
                    ->description('Choisissez le groupe à notifier.')
                    ->schema([
                        Select::make('group_id')
                            ->label('Groupe')
                            ->options(fn (): array => Group::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                $group = $state !== null ? Group::find($state) : null;
                                $set('subject', $group instanceof Group
                                    ? "Conseil du XX - {$group->name}"
                                    : null);
                            }),
                    ]),
                Section::make('Message')
                    ->visible(fn (Get $get): bool => filled($get('group_id')))
                    ->schema([
                        TextInput::make('subject')
                            ->label('Objet')
                            ->required()
                            ->maxLength(255),
                        RichEditor::make('body')
                            ->label('Message')
                            ->required(),
                    ]),
                Section::make('Pièces jointes')
                    ->visible(fn (Get $get): bool => filled($get('group_id')))
                    ->schema(fn (Get $get): array => $this->attachmentFields($get('group_id'))),
            ]);
    }

    public function send(): void
    {
        $data = $this->form->getState();

        $group = Group::query()
            ->with(['recipients', 'attachments'])
            ->findOrFail($data['group_id']);

        $recipients = $group->recipients->filter(
            static fn (Recipient $recipient): bool => filled($recipient->email),
        );

        if ($recipients->isEmpty()) {
            Notification::make()
                ->title('Aucun destinataire')
                ->body('Ce groupe ne contient aucun destinataire avec une adresse e-mail.')
                ->warning()
                ->send();

            return;
        }

        $files = [];

        foreach ($group->attachments as $attachment) {
            $path = $data["attachment_{$attachment->id}"] ?? null;

            if (filled($path)) {
                $files[] = [
                    'disk' => 'local',
                    'path' => $path,
                    'name' => $this->attachmentFileName($attachment, (string) $path),
                ];
            }
        }

        foreach ($recipients as $recipient) {
            Mail::to($recipient->email)->send(
                new ConseilNotificationMail($data['subject'], $data['body'], $files),
            );
        }

        Notification::make()
            ->title('Notifications envoyées')
            ->body(sprintf('%d destinataire(s) notifié(s).', $recipients->count()))
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
                ->label('Envoyer les notifications')
                ->icon(Heroicon::PaperAirplane)
                ->submit('send')
                ->requiresConfirmation()
                ->modalHeading('Confirmer l\'envoi')
                ->modalDescription('Un e-mail sera envoyé à chaque destinataire du groupe.')
                ->disabled(fn (): bool => blank($this->data['group_id'] ?? null)),
        ];
    }

    /**
     * Build one file upload field per attachment defined on the selected group.
     *
     * @return array<int, FileUpload|Text>
     */
    private function attachmentFields(?int $groupId): array
    {
        if (blank($groupId)) {
            return [];
        }

        $group = Group::with('attachments')->find($groupId);

        if (! $group instanceof Group || $group->attachments->isEmpty()) {
            return [
                Text::make('Aucune pièce jointe n\'est définie pour ce groupe.'),
            ];
        }

        return $group->attachments
            ->map(fn (Attachment $attachment): FileUpload => FileUpload::make("attachment_{$attachment->id}")
                ->label($attachment->name)
                ->helperText($attachment->description)
                ->disk('local')
                ->directory(config('conseil.uploads.notifications_directory'))
                ->visibility('private')
                ->maxSize(20480))
            ->values()
            ->all();
    }

    /**
     * Resolve a human-readable file name, keeping the uploaded extension.
     */
    private function attachmentFileName(Attachment $attachment, string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $name = $attachment->name;

        if ($extension !== '' && ! Str::endsWith(Str::lower($name), '.'.Str::lower($extension))) {
            $name .= '.'.$extension;
        }

        return $name;
    }
}
