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
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(fn (Recipient $r): array => [$r->id => "{$r->last_name} {$r->first_name}"]);
    }

    public static function getWithEmail(): Collection
    {
        return Recipient::query()
            ->whereNotNull('email')
            ->with('services')
            ->get();
    }

    /**
     * Recipients (with an email) that may be picked as share targets.
     *
     * @return Collection<int, string> keyed by recipient id
     */
    public static function getShareOptions(): Collection
    {
        return Recipient::query()
            ->whereNotNull('email')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(fn (Recipient $r): array => [$r->id => "{$r->last_name} {$r->first_name}"]);
    }

    /**
     * Recipients who may read the department's attachments: those flagged to
     * receive attachments and, when a department is given, linked to a service
     * of that department. Used as the pre-filled "who can read" list of an ask.
     *
     * @return Collection<int, string> keyed by recipient id
     */
    public static function getAttachmentReaderOptions(?string $department): Collection
    {
        return Recipient::query()
            ->whereNotNull('email')
            ->where('receives_attachments', true)
            ->when(
                $department !== null,
                fn (Builder $query): Builder => $query->whereHas(
                    'services',
                    fn (Builder $service): Builder => $service->where('courrier_services.department', $department),
                ),
            )
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(fn (Recipient $r): array => [$r->id => "{$r->last_name} {$r->first_name}"]);
    }

    public static function queryOrderByLastName(Builder $builder): Builder
    {
        return $builder->orderBy('last_name');
    }
}
