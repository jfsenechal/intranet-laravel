<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Pages;

use AcMarche\Courrier\Filament\Resources\IncomingMails\IncomingMailResource;
use AcMarche\Courrier\Models\Attachment;
use AcMarche\Courrier\Models\Sender;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Override;

final class CreateIncomingMail extends CreateRecord
{
    #[Override]
    protected static string $resource = IncomingMailResource::class;

    /** @var array<int> */
    private array $primaryServices = [];

    /** @var array<int> */
    private array $secondaryServices = [];

    /** @var array<int> */
    private array $primaryRecipients = [];

    /** @var array<int> */
    private array $secondaryRecipients = [];

    private bool $saveSender = false;

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return 'Ajouter un courrier';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->primaryServices = $data['primary_services'] ?? [];
        $this->secondaryServices = $data['secondary_services'] ?? [];
        $this->primaryRecipients = $data['primary_recipients'] ?? [];
        $this->secondaryRecipients = $data['secondary_recipients'] ?? [];
        $this->saveSender = (bool) ($data['save_sender'] ?? false);

        unset(
            $data['attachment_file'],
            $data['primary_services'],
            $data['secondary_services'],
            $data['primary_recipients'],
            $data['secondary_recipients'],
            $data['save_sender'],
        );

        return $data;
    }

    protected function afterCreate(): void
    {
        // Save sender to senders table if checkbox was checked
        if ($this->saveSender && $this->record->sender) {
            Sender::firstOrCreate(['name' => $this->record->sender]);
        }

        // Attach services and recipients via pivot tables
        foreach ($this->primaryServices as $serviceId) {
            $this->record->services()->attach($serviceId, ['is_primary' => true]);
        }

        foreach ($this->secondaryServices as $serviceId) {
            $this->record->services()->attach($serviceId, ['is_primary' => false]);
        }

        foreach ($this->primaryRecipients as $recipientId) {
            $this->record->recipients()->attach($recipientId, ['is_primary' => true]);
        }

        foreach ($this->secondaryRecipients as $recipientId) {
            $this->record->recipients()->attach($recipientId, ['is_primary' => false]);
        }

        // Handle file attachment
        $file = $this->data['attachment_file'] ?? null;

        if (! $file instanceof TemporaryUploadedFile) {
            return;
        }

        $originalName = $file->getClientOriginalName();
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $storedFilename = sprintf(
            '%d_%s.%s',
            $this->record->id,
            Str::slug(pathinfo($originalName, PATHINFO_FILENAME)),
            $extension
        );

        $path = config('courrier.storage.directory')."/attachments/{$storedFilename}";
        Storage::disk(config('courrier.storage.disk'))->put($path, $file->readStream());

        Attachment::create([
            'incoming_mail_id' => $this->record->id,
            'file_name' => $storedFilename,
            'mime' => $file->getMimeType(),
        ]);
    }
}
