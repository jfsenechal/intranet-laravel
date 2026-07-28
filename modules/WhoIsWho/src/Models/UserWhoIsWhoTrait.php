<?php

declare(strict_types=1);

namespace AcMarche\WhoIsWho\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait UserWhoIsWhoTrait
{
    /**
     * The directory entries the user marked as favorite, oldest first.
     *
     * @return HasMany<FavoriteEmployee, $this>
     */
    public function employeeFavorites(): HasMany
    {
        return $this->hasMany(FavoriteEmployee::class)->oldest();
    }

    public function hasFavoriteEmployee(int $employeeId): bool
    {
        return $this->employeeFavorites()
            ->where('employee_id', $employeeId)
            ->exists();
    }

    /**
     * Toggle an employee in the user's favorites. Returns true when the employee
     * is now a favorite, false when it was removed.
     */
    public function toggleFavoriteEmployee(int $employeeId): bool
    {
        $removed = $this->employeeFavorites()
            ->where('employee_id', $employeeId)
            ->delete();

        if ($removed === 0) {
            $this->employeeFavorites()->create(['employee_id' => $employeeId]);
        }

        $this->unsetRelation('employeeFavorites');

        return $removed === 0;
    }
}
