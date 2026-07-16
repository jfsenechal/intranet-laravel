<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Http\Middleware;

use AcMarche\EmailManagement\Enums\RolesEnum;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts the whole email-management panel to ROLE_EMAIL_ADMIN.
 *
 * App\Models\User::canAccessPanel() returns true for every panel, so panel access
 * is gated here rather than there: changing that method would affect every other
 * panel in the intranet.
 */
final class EnsureEmailAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $this->isEmailAdmin($user)) {
            abort(403, "Vous n'avez pas accès à la gestion des emails.");
        }

        return $next($request);
    }

    private function isEmailAdmin(User $user): bool
    {
        return $user->isAdministrator() || $user->hasRole(RolesEnum::ROLE_EMAIL_ADMIN->value);
    }
}
