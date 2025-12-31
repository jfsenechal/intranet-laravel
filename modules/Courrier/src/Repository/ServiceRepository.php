<?php

namespace AcMarche\Courrier\Repository;

use AcMarche\Courrier\Models\Service;
use Illuminate\Support\Collection;

final class ServiceRepository
{
    public static function findAllActiveOrdered(): Collection
    {
        return Service::query()->orderBy('name')->where('is_active', true)->pluck('name', 'id');
    }
}
