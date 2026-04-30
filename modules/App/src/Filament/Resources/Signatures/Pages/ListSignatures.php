<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Signatures\Pages;

use AcMarche\App\Filament\Resources\Signatures\SignatureResource;
use AcMarche\App\Models\Signature;
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

        $signature = Signature::query()->where('username', Auth::user()->username)->first();

        if ($signature instanceof Signature) {
            $this->redirect(SignatureResource::getUrl('view', ['record' => $signature]));

            return;
        }

        $this->redirect(SignatureResource::getUrl('create'));
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
