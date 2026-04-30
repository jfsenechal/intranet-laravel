<?php

declare(strict_types=1);

namespace AcMarche\Pst\Repository;

use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\ActionUser;
use AcMarche\Pst\Models\ServiceUser;
use Illuminate\Database\Eloquent\Builder;

final class ActionRepository
{
    public static function findByUser(int $userId): Builder
    {
        return Action::query()->whereIn('id',
            ActionUser::select('action_id')
                ->whereHas('user', fn ($q) => $q->where('id', $userId))
        );
    }

    public static function findByUserServices(int $userId): Builder
    {
        $serviceIds = ServiceUser::select('service_id')
            ->whereHas('user', fn ($q) => $q->where('id', $userId));

        return Action::query()
            ->whereHas('leaderServices', fn ($q) => $q->whereIn('services.id', $serviceIds))
            ->orWhereHas('partnerServices', fn ($q) => $q->whereIn('services.id', $serviceIds));
    }
}
