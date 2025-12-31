<?php

namespace AcMarche\Courrier\Repository;

use AcMarche\Courrier\Models\Recipient;
use Illuminate\Support\Collection;

final class RecipientRepository
{
    public static function getActiveForOptions(): Collection
    {
        return Recipient::query()
            ->orderBy('last_name')
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(fn (Recipient $r) => [$r->id => "{$r->first_name} {$r->last_name}"]);
    }
}
