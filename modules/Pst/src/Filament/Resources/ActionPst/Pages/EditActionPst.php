<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\ActionPst\Pages;

use AcMarche\Pst\Filament\Resources\ActionPst\ActionPstResource;
use AcMarche\Pst\Models\Service;
use AcMarche\Pst\Models\TracksHistoryTrait;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditActionPst extends EditRecord
{
    use TracksHistoryTrait;

    #[Override]
    protected static string $resource = ActionPstResource::class;

    /**
     * @var array<string, array<int>>
     */
    private array $oldRelationshipIds = [];

    /**
     * to remove word "editer"
     */
    public function getTitle(): string
    {
        return $this->getRecord()->name;
    }

    /**
     * Hide relation managers on Edit page - they are shown on View page only.
     */
    protected function getAllRelationManagers(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }

    protected function beforeSave(): void
    {
        $record = $this->getRecord();

        $this->oldRelationshipIds = [
            'users' => $record->users()->pluck('users.username')->toArray(),
            'leaderServices' => $record->leaderServices()->pluck('services.id')->toArray(),
            'partnerServices' => $record->partnerServices()->pluck('services.id')->toArray(),
            'mandataries' => $record->mandataries()->pluck('users.username')->toArray(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        $relationships = [
            'users' => [
                'old' => $this->oldRelationshipIds['users'],
                'new' => $record->users()->pluck('users.username')->toArray(),
                'label' => 'agent pilote',
                'getDisplayName' => $this->getUserDisplayName(...),
            ],
            'leaderServices' => [
                'old' => $this->oldRelationshipIds['leaderServices'],
                'new' => $record->leaderServices()->pluck('services.id')->toArray(),
                'label' => 'service porteur',
                'getDisplayName' => $this->getServiceDisplayName(...),
            ],
            'partnerServices' => [
                'old' => $this->oldRelationshipIds['partnerServices'],
                'new' => $record->partnerServices()->pluck('services.id')->toArray(),
                'label' => 'service partenaire',
                'getDisplayName' => $this->getServiceDisplayName(...),
            ],
            'mandataries' => [
                'old' => $this->oldRelationshipIds['mandataries'],
                'new' => $record->mandataries()->pluck('users.username')->toArray(),
                'label' => 'mandataire',
                'getDisplayName' => $this->getUserDisplayName(...),
            ],
        ];

        $this->trackRelationships($record, $relationships);
    }

    private function getUserDisplayName(string $username): string
    {
        $user = User::query()->where('username', $username)->first();

        return $user ? "{$user->first_name} {$user->last_name}" : $username;
    }

    private function getServiceDisplayName(int $id): string
    {
        $service = Service::find($id);

        return $service?->name ?? "ID: {$id}";
    }
}
