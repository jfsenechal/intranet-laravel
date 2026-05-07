<?php

declare(strict_types=1);

namespace AcMarche\WhoIsWho\Repository;

use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

final class EmployeeRepository
{
    /**
     * @return Builder<Employee>
     */
    public static function activeAgentsQuery(): Builder
    {
        return Employee::query()
            ->where('status', StatusEnum::AGENT->value)
            ->where('is_archived', false)
            ->whereHas('activeContracts')
            ->with(['activeContracts.service'])
            ->orderBy('last_name')
            ->orderBy('first_name');
    }

    /**
     * @return Collection<int, Employee>
     */
    public static function activeAgents(): Collection
    {
        return self::activeAgentsQuery()->get();
    }

    /**
     * @return Collection<int, Employee>
     */
    public static function search(?string $term): Collection
    {
        $query = self::activeAgentsQuery();

        if ($term === null || mb_trim($term) === '') {
            return new Collection();
        }

        $term = mb_trim($term);

        return $query->where(function (Builder $query) use ($term): void {
            $query->where('last_name', 'like', '%'.$term.'%')
                ->orWhere('first_name', 'like', '%'.$term.'%')
                ->orWhereHas('activeContracts.service', function (Builder $query) use ($term): void {
                    $query->where('name', 'like', '%'.$term.'%');
                });
        })->get();
    }

    /**
     * @return Collection<int, Service>
     */
    public static function servicesWithAgents(): Collection
    {
        return Service::query()
            ->with(['direction', 'employer'])
            ->whereHas('contracts', function (Builder $query): void {
                $query->active()
                    ->whereHas('employee', function (Builder $query): void {
                        $query->where('status', StatusEnum::AGENT->value)
                            ->where('is_archived', false);
                    });
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Employee>
     */
    public static function agentsByService(int $serviceId): Collection
    {
        return self::activeAgentsQuery()
            ->whereHas('activeContracts', function (Builder $query) use ($serviceId): void {
                $query->where('service_id', $serviceId);
            })
            ->get();
    }

    /**
     * Group active agents by the first letter of their last name.
     *
     * @return Collection<string, Collection<int, Employee>>
     */
    public static function groupedByLetter(): Collection
    {
        return self::activeAgents()->groupBy(
            fn (Employee $employee): string => mb_strtoupper(mb_substr((string) $employee->last_name, 0, 1)) ?: '#'
        )->sortKeys();
    }

    /**
     * Resolve the employee's photo URL using a 3-tier fallback:
     * 1. The User profile avatar matched by `username` (set via Filament profile page).
     * 2. The Employee `photo` from HRM, when `show_photo` is enabled.
     * 3. A generated default avatar based on the employee's name.
     */
    public static function photoUrl(Employee $employee): string
    {
        if (filled($employee->username)) {
            $user = User::query()
                ->where('username', $employee->username)
                ->first();

            if ($user instanceof User && filled($user->avatar_url)) {
                return Storage::disk('public')->url($user->avatar_url);
            }
        }

        if ($employee->show_photo && filled($employee->photo)) {
            return Storage::disk('public')->url($employee->photo);
        }

        $fullName = mb_trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));

        return 'https://ui-avatars.com/api/?size=160&name='.urlencode($fullName !== '' ? $fullName : '?');
    }
}
