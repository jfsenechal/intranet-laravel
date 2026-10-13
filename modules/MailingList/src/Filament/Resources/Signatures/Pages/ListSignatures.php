<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Signatures\Pages;

use AcMarche\MailingList\Filament\Resources\Signatures\SignatureResource;
use AcMarche\MailingList\Models\Signature;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Override;

final class ListSignatures extends ListRecords
{
    #[Override]
    protected static string $resource = SignatureResource::class;

    public function mount(): void
    {
        parent::mount();

        if (! Signature::query()->where('username', Auth::user()->username)->exists()) {
            $this->redirect(SignatureResource::getUrl('create'));
        }
    }

    public function getTitle(): string
    {
        return 'Mes signatures';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nouvelle signature'),
        ];
    }
}
