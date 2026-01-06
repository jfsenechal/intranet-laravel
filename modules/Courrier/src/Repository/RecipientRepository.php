<?php

namespace AcMarche\Courrier\Repository;

use AcMarche\Courrier\Models\Recipient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class RecipientRepository
{
    public static function getActiveForOptions(): Collection
    {
        return Recipient::query()
            ->orderBy('last_name')
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(fn(Recipient $r) => [$r->id => "{$r->first_name} {$r->last_name}"]);
    }

    public static function getActiveAndWithEmail(): Collection
    {
        return Recipient::query()
            ->where('is_active', true)
            ->whereNotNull('email')
            ->with('services')
            ->get();
    }

    public static function queryActiveOrderByLastName(Builder $builder): Builder
    {
        return $builder->where('is_active', true)
            ->orderBy('last_name');
    }
}
