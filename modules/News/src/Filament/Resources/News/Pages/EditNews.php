<?php

declare(strict_types=1);

namespace AcMarche\News\Filament\Resources\News\Pages;

use AcMarche\News\Events\NewsProcessed;
use AcMarche\News\Filament\Resources\News\NewsResource;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditNews extends EditRecord
{
    #[Override]
    protected static string $resource = NewsResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }

    /**
     * The mail is only resent when the author explicitly asks for it, the news
     * having already been notified when it was created.
     */
    protected function afterSave(): void
    {
        if (! ($this->data['resend_mail'] ?? false)) {
            return;
        }

        event(new NewsProcessed($this->record));

        $this->data['resend_mail'] = false;

        Notification::make()
            ->title('Le mail a été renvoyé aux utilisateurs concernés')
            ->success()
            ->send();
    }
}
