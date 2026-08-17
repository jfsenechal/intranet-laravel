<?php

declare(strict_types=1);

namespace AcMarche\Issep\Http\Middleware;

use AcMarche\Issep\Enums\RolesEnum;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts the whole issep panel to ROLE_CAPTEUR, which is what the legacy intranet
 * required on every one of its controllers.
 *
 * App\Models\User::canAccessPanel() returns true for every panel, so panel access is gated
 * here rather than there.
 */
final class EnsureCapteurAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $this->hasAccess($user)) {
            abort(403, "Vous n'avez pas accès aux capteurs d'air.");
        }

        return $next($request);
    }

    private function hasAccess(User $user): bool
    {
        return $user->isAdministrator() || $user->hasRole(RolesEnum::ROLE_CAPTEUR->value);
    }
}
