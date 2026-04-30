<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Repository;

use AcMarche\Courrier\Models\Recipient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class RecipientRepository
{
    public static function getForOptions(): Collection
    {
        return Recipient::query()
            ->orderBy('last_name')
            ->get()
            ->mapWithKeys(fn (Recipient $r): array => [$r->id => "{$r->first_name} {$r->last_name}"]);
    }

    public static function getWithEmail(): Collection
    {
        return Recipient::query()
            ->whereNotNull('email')
            ->with('services')
            ->get();
    }

    public static function queryOrderByLastName(Builder $builder): Builder
    {
        return $builder->orderBy('last_name');
    }
}
