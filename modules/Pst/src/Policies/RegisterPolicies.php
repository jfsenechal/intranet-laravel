<?php

declare(strict_types=1);

namespace AcMarche\Pst\Policies;

use AcMarche\Pst\Models\Action;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class RegisterPolicies
{
    use ActionEditPolicyTrait;

    public static function register(): void
    {
        Gate::define('teams-edit', function (User $user, Action $action, string $operation): bool {

            if ($operation === 'create') {
                return true;
            }

            return self::isUserLinkedToAction($user, $action);

        });
    }
}
