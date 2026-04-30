<?php

declare(strict_types=1);

namespace AcMarche\Agent\Concerns;

use AcMarche\Agent\Models\History;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;

// https://laravel.com/docs/13.x/eloquent#examining-attribute-changes
trait TracksHistoryTrait
{
    /**
     * Track scalar / fillable attribute changes on the given profile.
     *
     * @param  array<int, string>  $ignore
     */
    protected function track(Model $profile, array $ignore = ['created_at', 'updated_at', 'uuid']): void
    {
        foreach ($profile->getChanges() as $key => $newValue) {
            if (in_array($key, $ignore, true)) {
                continue;
            }

            $oldValue = $profile->getOriginal($key);

            if ($this->normalizeValue($oldValue) === $this->normalizeValue($newValue)) {
                continue;
            }

            History::create([
                'profile_id' => $profile->id,
                'name' => str_replace('_', ' ', $key),
                'old_value' => $this->normalizeValue($oldValue),
                'new_value' => $this->normalizeValue($newValue),
                'username' => auth()->user()?->username ?? 'import',
            ]);
        }
    }

    /**
     * Track changes to a BelongsToMany relationship by comparing id lists.
     *
     * @param  array<int, int|string>  $oldIds
     * @param  array<int, int|string>  $newIds
     * @param  callable(int|string): string  $getDisplayName
     */
    protected function trackRelationIds(
        Model $profile,
        string $name,
        array $oldIds,
        array $newIds,
        callable $getDisplayName,
    ): void {
        sort($oldIds);
        sort($newIds);

        if ($oldIds === $newIds) {
            return;
        }

        History::create([
            'profile_id' => $profile->id,
            'name' => $name,
            'old_value' => array_map(static fn ($id): string => $getDisplayName($id), $oldIds),
            'new_value' => array_map(static fn ($id): string => $getDisplayName($id), $newIds),
            'username' => auth()->user()?->username ?? 'import',
        ]);
    }

    /**
     * Track changes on a HasOne related record by comparing attribute arrays.
     *
     * @param  array<string, mixed>  $oldAttributes
     * @param  array<string, mixed>  $newAttributes
     */
    protected function trackRelationAttributes(
        Model $profile,
        string $name,
        array $oldAttributes,
        array $newAttributes,
    ): void {
        $oldAttributes = $this->filterRelationAttributes($oldAttributes);
        $newAttributes = $this->filterRelationAttributes($newAttributes);

        if ($oldAttributes === $newAttributes) {
            return;
        }

        History::create([
            'profile_id' => $profile->id,
            'name' => $name,
            'old_value' => $oldAttributes,
            'new_value' => $newAttributes,
            'username' => auth()->user()?->username ?? 'import',
        ]);
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function filterRelationAttributes(array $attributes): array
    {
        foreach (['id', 'profile_id', 'created_at', 'updated_at'] as $key) {
            unset($attributes[$key]);
        }

        return $attributes;
    }
}
