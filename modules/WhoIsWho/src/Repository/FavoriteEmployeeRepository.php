<?php

declare(strict_types=1);

namespace AcMarche\WhoIsWho\Repository;

use AcMarche\Hrm\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class FavoriteEmployeeRepository
{
    /**
     * The ids of the employees the user marked as favorite, oldest first.
     *
     * @return list<int>
     */
    public static function favoriteIds(?User $user = null): array
    {
        $user ??= auth()->user();

        if (! $user instanceof User) {
            return [];
        }

        return $user->employeeFavorites()
            ->pluck('employee_id')
            ->map(fn (int $id): int => $id)
            ->all();
    }

    /**
     * The favorite agents still listed in the directory. An agent whose contract
     * ended, who was archived or who is no longer an agent simply drops out of
     * the list, exactly like on the other directory pages.
     *
     * @return Collection<int, Employee>
     */
    public static function favorites(?User $user = null): Collection
    {
        $favoriteIds = self::favoriteIds($user);

        if ($favoriteIds === []) {
            return new Collection();
        }

        return EmployeeRepository::activeAgentsQuery()
            ->whereIn('id', $favoriteIds)
            ->get();
    }
}
