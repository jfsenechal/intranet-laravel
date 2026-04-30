<?php

declare(strict_types=1);

namespace AcMarche\Pst\Models;

use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

// https://medium.com/sammich-shop/simple-record-history-tracking-with-laravel-observers-48a2e3c5698b
// https://laravel.com/docs/12.x/eloquent#examining-attribute-changes
trait TracksHistoryTrait
{
    protected function track(Model $model, ?callable $func = null, $table = null, $id = null): void
    {
        $id = $id ?: $model->id;
        // Allow for customization of the history record if needed
        $func = $func ?: [$this, 'getHistoryBody'];

        // Get the dirty fields and run them through the custom function, then insert them into the history table
        $this->getUpdated($model)
            ->map(fn ($value, $field): mixed => call_user_func_array($func, [$value, $field]))
            ->each(function ($fields) use ($id): void {
                History::create(
                    [
                        'action_id' => $id,
                        'user_add' => auth()->user()?->username ?? 'import',
                    ] + $fields
                );
            });
    }

    /**
     * Track changes to BelongsToMany relationships.
     *
     * @param  array<string, array{old: array<int>, new: array<int>, label: string, getDisplayName: callable}>  $relationships
     */
    protected function trackRelationships(Model $model, array $relationships): void
    {
        foreach ($relationships as $relationName => $config) {
            $oldIds = collect($config['old']);
            $newIds = collect($config['new']);
            $label = $config['label'];
            $getDisplayName = $config['getDisplayName'];

            $attached = $newIds->diff($oldIds);
            $detached = $oldIds->diff($newIds);

            foreach ($attached as $id) {
                $displayName = $getDisplayName($id);
                $body = Str::limit("Ajouté $label: $displayName", 150);
                History::create([
                    'action_id' => $model->id,
                    'user_add' => auth()->user()?->username ?? 'import',
                    'body' => $body,
                    'property' => $relationName,
                    'new_value' => $displayName,
                ]);
            }

            foreach ($detached as $id) {
                $displayName = $getDisplayName($id);
                $body = Str::limit("Retiré $label: $displayName", 150);
                History::create([
                    'action_id' => $model->id,
                    'user_add' => auth()->user()?->username ?? 'import',
                    'body' => $body,
                    'property' => $relationName,
                    'old_value' => $displayName,
                ]);
            }
        }
    }

    protected function getHistoryBody($value, $field): array
    {
        $displayValue = $value instanceof BackedEnum ? $value->value : $value;
        $body = Str::limit("Mis à jour $field to $displayValue", 150);

        return [
            'body' => $body,
            'property' => $field,
            'new_value' => $displayValue,
        ];
    }

    protected function getUpdated($model): Collection
    {
        return collect($model->getDirty())->reject(fn ($value, $key): bool => in_array($key, ['created_at', 'updated_at']))->mapWithKeys(
            // Take the field names and convert them into human readable strings for the description of the action
            // e.g. first_name -> first name
            fn ($value, $key): array => [str_replace('_', ' ', $key) => $value]);
    }
}
