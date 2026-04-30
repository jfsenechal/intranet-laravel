<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Forms\Components;

use AcMarche\Agent\Models\Folder;
use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Model;

final class FolderBrowser extends Field
{
    protected string $view = 'agent::forms.components.folder-browser';

    protected string $relationshipName = 'folders';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);
        $this->dehydrated(false);

        $this->afterStateHydrated(function (self $component, ?Model $record): void {
            $ids = $record?->{$this->relationshipName}->pluck('id')->all() ?? [];
            $component->state(array_values(array_map('intval', $ids)));
        });

        $this->saveRelationshipsUsing(function (Model $record, array $state): void {
            $record->{$this->relationshipName}()->sync(array_values(array_unique(array_map('intval', $state))));
        });
    }

    public function relationship(string $name): static
    {
        $this->relationshipName = $name;

        return $this;
    }

    /**
     * Tree keyed by parent_id ("0" for roots): [parent_id => [['id' => int, 'name' => string], ...]].
     *
     * @return array<int|string, list<array{id: int, name: string}>>
     */
    public function getTree(): array
    {
        return Folder::query()
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name'])
            ->groupBy(fn (Folder $folder): string => (string) ($folder->parent_id ?? 0))
            ->map(fn ($group) => $group
                ->map(fn (Folder $folder): array => [
                    'id' => (int) $folder->id,
                    'name' => $folder->name,
                ])
                ->values()
                ->all())
            ->all();
    }

    /**
     * Breadcrumb labels per folder id: [id => "root / child / leaf"].
     *
     * @return array<int, string>
     */
    public function getBreadcrumbs(): array
    {
        $folders = Folder::query()
            ->get(['id', 'parent_id', 'name'])
            ->keyBy('id');

        $breadcrumbs = [];
        foreach ($folders as $folder) {
            $path = [];
            $cursor = $folder;
            while ($cursor instanceof Folder) {
                array_unshift($path, $cursor->name);
                $cursor = $cursor->parent_id !== null ? $folders->get($cursor->parent_id) : null;
            }
            $breadcrumbs[(int) $folder->id] = implode(' / ', $path);
        }

        return $breadcrumbs;
    }
}
