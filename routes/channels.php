<?php

declare(strict_types=1);

use AcMarche\GuichetHdv\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('guichet-hdv.tickets', function (User $user): bool {
    return $user->hasOneOfThisRoles([
        RolesEnum::ROLE_GUICHET_AGENT->value,
        RolesEnum::ROLE_GUICHET->value,
    ]);
});
