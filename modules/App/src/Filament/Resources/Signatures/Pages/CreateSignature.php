<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Signatures\Pages;

use AcMarche\App\Filament\Resources\Signatures\SignatureResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Override;

final class CreateSignature extends CreateRecord
{
    #[Override]
    protected static string $resource = SignatureResource::class;

    public function getTitle(): string
    {
        return 'Créer ma signature';
    }

    protected function fillForm(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        $this->form->fill([
            'first_name' => $user?->first_name,
            'last_name' => $user?->last_name,
            'email' => $user?->email,
            'username' => $user?->username,
            'phone' => $user?->phone,
            'mobile' => $user?->mobile,
            'postal_code' => 6900,
            'city' => 'Marche-en-Famenne',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usernme'] = Auth::user()->username;

        return $data;
    }
}
