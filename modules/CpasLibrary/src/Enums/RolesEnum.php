<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Enums;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

enum RolesEnum: string
{
    case ROLE_LIBRARY_ADMIN = 'ROLE_LIBRARY_ADMIN';
    case ROLE_LIBRARY = 'ROLE_LIBRARY';

    /**
     * Every user holding a library role and reachable by email.
     *
     * @return Collection<int, User>
     */
    public static function libraryUsers(): Collection
    {
        return User::query()
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', array_column(self::cases(), 'value')))
            ->whereNotNull('email')
            ->get();
    }
}
