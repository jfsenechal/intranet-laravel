<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Profiles\Pages;

use AcMarche\Agent\Concerns\TracksHistoryTrait;
use AcMarche\Agent\Filament\Resources\Profiles\ProfileResource;
use AcMarche\Agent\Models\ExternalApplication;
use AcMarche\Agent\Models\Folder;
use AcMarche\Security\Models\Module;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditProfile extends EditRecord
{
    use TracksHistoryTrait;

    #[Override]
    protected static string $resource = ProfileResource::class;

    /**
     * @var array<string, array<int, int|string>>
     */
    private array $oldRelationIds = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $oldRelationAttributes = [];

    public function getTitle(): string|Htmlable
    {
        return 'Profil de '.$this->record->fullName();
    }

    public function getAllRelationManagers(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon(Heroicon::Eye),
        ];
    }

    protected function beforeSave(): void
    {
        $profile = $this->getRecord();

        $this->oldRelationIds = [
            'externalApplications' => $profile->externalApplications()->pluck('external_applications.id')->all(),
            'folders' => $profile->folders()->pluck('folders.id')->all(),
        ];

        $this->oldRelationAttributes = [
            'phone' => $profile->phone?->getAttributes() ?? [],
            'hardware' => $profile->hardware?->getAttributes() ?? [],
        ];
    }

    protected function afterSave(): void
    {
        $profile = $this->getRecord();

        $oldModules = (array) ($profile->getOriginal('modules') ?? []);
        $newModules = (array) ($profile->modules ?? []);

        $this->track($profile, ignore: ['created_at', 'updated_at', 'uuid', 'modules']);

        $this->trackRelationIds(
            $profile,
            'modules',
            $oldModules,
            $newModules,
            static fn ($id): string => Module::find($id)?->name ?? "ID: {$id}",
        );

        $this->trackRelationIds(
            $profile,
            'applications externes',
            $this->oldRelationIds['externalApplications'],
            $profile->externalApplications()->pluck('external_applications.id')->all(),
            static fn ($id): string => ExternalApplication::find($id)?->name ?? "ID: {$id}",
        );

        $this->trackRelationIds(
            $profile,
            'dossiers',
            $this->oldRelationIds['folders'],
            $profile->folders()->pluck('folders.id')->all(),
            static fn ($id): string => Folder::find($id)?->name ?? "ID: {$id}",
        );

        $profile->refresh();

        $this->trackRelationAttributes(
            $profile,
            'téléphonie',
            $this->oldRelationAttributes['phone'],
            $profile->phone?->getAttributes() ?? [],
        );

        $this->trackRelationAttributes(
            $profile,
            'matériel',
            $this->oldRelationAttributes['hardware'],
            $profile->hardware?->getAttributes() ?? [],
        );
    }
}
