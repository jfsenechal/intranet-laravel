<?php

declare(strict_types=1);

namespace AcMarche\Pst\Models;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Pst\Enums\RolesEnum;
use AcMarche\Pst\Providers\PstServiceProvider;
use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use AcMarche\Security\Repository\UserRepository;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait UserPstTrait
{
    /**
     * @return BelongsToMany<Service>
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_user', 'username', 'service_id', 'username', 'id');
    }

    /**
     * @return BelongsToMany<Action>
     */
    public function actions(): BelongsToMany
    {
        return $this->belongsToMany(Action::class, 'action_user', 'username', 'action_id', 'username', 'id');
    }

    /**
     * @return Builder<Action>
     */
    public function actionsFromServices(): Builder
    {
        $serviceIds = $this->services()->select('pst_services.id');

        return Action::query()->where(
            static function (Builder $query) use ($serviceIds): void {
                $query->whereHas('leaderServices', fn ($q) => $q->whereIn('pst_services.id', $serviceIds))
                    ->orWhereHas('partnerServices', fn ($q) => $q->whereIn('pst_services.id', $serviceIds));
            }
        );
    }

    /**
     * The departments whose PST the user works on, given by the PST department roles.
     *
     * @return list<string>
     */
    public function pstDepartments(): array
    {
        $departments = [];
        foreach (DepartmentEnum::cases() as $department) {
            if ($this->hasRole(RolesEnum::forDepartment($department)->value)) {
                $departments[] = $department->value;
            }
        }

        return $departments;
    }

    /**
     * Give the user the PST department roles of the given departments and take
     * away the others, leaving their other roles alone.
     *
     * @param  list<string>  $departments
     */
    public function syncPstDepartments(array $departments): void
    {
        foreach (DepartmentEnum::cases() as $department) {
            $role = Role::query()->firstOrCreate(
                ['name' => RolesEnum::forDepartment($department)->value],
                ['module_id' => Module::query()->whereKey(PstServiceProvider::$module_id)->exists() ? PstServiceProvider::$module_id : null],
            );

            if (in_array($department->value, $departments, true)) {
                $this->addRole($role);
            } else {
                $this->roles()->detach($role);
                $this->unsetRelation('roles');
            }
        }
    }

    #[Scope]
    protected function forSelectedDepartment(Builder $query): void
    {
        $department = DepartmentEnum::tryFrom(UserRepository::departmentSelected()) ?? DepartmentEnum::VILLE;
        $query->whereHas(
            'roles',
            fn (Builder $query): Builder => $query->where('name', RolesEnum::forDepartment($department)->value),
        );
    }
}
