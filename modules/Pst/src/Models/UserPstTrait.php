<?php

declare(strict_types=1);

namespace AcMarche\Pst\Models;

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
        $serviceIds = $this->services()->select('services.id');

        return Action::query()->where(
            static function (Builder $query) use ($serviceIds): void {
                $query->whereHas('leaderServices', fn ($q) => $q->whereIn('services.id', $serviceIds))
                    ->orWhereHas('partnerServices', fn ($q) => $q->whereIn('services.id', $serviceIds));
            }
        );
    }

    #[Scope]
    protected function forSelectedDepartment(Builder $query): void
    {
        $department = UserRepository::departmentSelected();
        $query->whereJsonContains('departments', $department);
    }
}
