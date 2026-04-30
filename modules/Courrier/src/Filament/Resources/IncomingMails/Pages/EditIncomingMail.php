<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Pages;

use AcMarche\Courrier\Filament\Resources\IncomingMails\IncomingMailResource;
use AcMarche\Courrier\Models\Sender;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditIncomingMail extends EditRecord
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

    public function getTitle(): string
    {
        return 'Modifier le courrier';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['primary_services'] = $this->record->services()->wherePivot('is_primary', true)->pluck('courrier_services.id')->toArray();
        $data['secondary_services'] = $this->record->services()->wherePivot('is_primary', false)->pluck('courrier_services.id')->toArray();
        $data['primary_recipients'] = $this->record->recipients()->wherePivot('is_primary', true)->pluck('recipients.id')->toArray();
        $data['secondary_recipients'] = $this->record->recipients()->wherePivot('is_primary', false)->pluck('recipients.id')->toArray();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function afterSave(): void
    {
        // Save sender to senders table if checkbox was checked
        if ($this->saveSender && $this->record->sender) {
            Sender::firstOrCreate(['name' => $this->record->sender]);
        }

        // Sync services
        $services = [];
        foreach ($this->primaryServices as $serviceId) {
            $services[$serviceId] = ['is_primary' => true];
        }
        foreach ($this->secondaryServices as $serviceId) {
            $services[$serviceId] = ['is_primary' => false];
        }
        $this->record->services()->sync($services);

        // Sync recipients
        $recipients = [];
        foreach ($this->primaryRecipients as $recipientId) {
            $recipients[$recipientId] = ['is_primary' => true];
        }
        foreach ($this->secondaryRecipients as $recipientId) {
            $recipients[$recipientId] = ['is_primary' => false];
        }
        $this->record->recipients()->sync($recipients);
    }
}
