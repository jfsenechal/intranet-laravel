<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Repository;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ServiceRepository
{
    public static function findAllActiveOrdered(): Collection
    {
        return Service::query()->orderBy('name')->pluck('name', 'id');
    }

    /**
     * Resolve the service codes stamped on an incoming mail to service ids.
     *
     * The codes are the initials a service is known by day to day (« RH »,
     * « CEE »). A code is kept only when it matches exactly one service: several
     * services share initials — MUS is both the Musée and the Conservatoire de
     * Musique — and picking one of them at random would silently misroute the
     * mail. Each department runs its own list, in which the same initials appear
     * again, so pass the department the mail is being encoded in.
     *
     * @param  array<int, string>  $codes
     * @return array<int, int> ids of the services that matched, without duplicates
     */
    public static function findIdsByCodes(array $codes, ?DepartmentCourrierEnum $department = null): array
    {
        $ids = [];

        foreach ($codes as $code) {
            $code = mb_strtolower(mb_trim($code));

            if ($code === '') {
                continue;
            }

            $matches = Service::query()
                ->when(
                    $department instanceof DepartmentCourrierEnum,
                    fn (Builder $query): Builder => $query->where('department', $department->value),
                )
                ->where(fn (Builder $query): Builder => $query
                    ->whereRaw('LOWER(initials) = ?', [$code])
                    ->orWhereRaw('LOWER(name) = ?', [$code]))
                ->pluck('id');

            if ($matches->count() === 1) {
                $ids[] = (int) $matches->first();
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Services whose name or initials match the search term. Services are
     * commonly referred to by their initials, so both are searched.
     *
     * @return Collection<int, string> keyed by service id
     */
    public static function searchByNameOrInitials(string $search, int $limit = 50): Collection
    {
        return Service::query()
            ->where(fn (Builder $query): Builder => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('initials', 'like', "%{$search}%"))
            ->orderBy('name')
            ->limit($limit)
            ->pluck('name', 'id');
    }
}
