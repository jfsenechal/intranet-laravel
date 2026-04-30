<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Folders\Schemas;

use AcMarche\Agent\Models\Folder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

final class FolderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        Select::make('parent_id')
                            ->label('Parent')
                            ->options(fn (?Folder $record): array => self::treeOptions($record))
                            ->searchable()
                            ->placeholder('— racine —'),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @return array<int, string>
     */
    private static function treeOptions(?Folder $record): array
    {
        $excluded = $record
            ? self::collectDescendantIds($record)->push($record->getKey())->all()
            : [];

        $folders = Folder::query()
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name'])
            ->reject(fn (Folder $folder): bool => in_array($folder->getKey(), $excluded, true));

        $grouped = $folders->groupBy(fn (Folder $folder): int => (int) ($folder->parent_id ?? 0));

        $options = [];
        self::appendTree($grouped, 0, 0, $options);

        return $options;
    }

    /**
     * @param  Collection<int, Collection<int, Folder>>  $grouped
     * @param  array<int, string>  $options
     */
    private static function appendTree(Collection $grouped, int $parentId, int $depth, array &$options): void
    {
        foreach ($grouped->get($parentId, collect()) as $folder) {
            $options[$folder->getKey()] = str_repeat('— ', $depth).$folder->name;
            self::appendTree($grouped, (int) $folder->getKey(), $depth + 1, $options);
        }
    }

    /**
     * @return Collection<int, int>
     */
    private static function collectDescendantIds(Folder $folder): Collection
    {
        $ids = collect();
        foreach ($folder->children as $child) {
            $ids->push($child->getKey());
            $ids = $ids->merge(self::collectDescendantIds($child));
        }

        return $ids;
    }
}
