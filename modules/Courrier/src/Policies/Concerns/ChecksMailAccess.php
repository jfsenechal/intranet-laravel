<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Policies\Concerns;

use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use App\Models\User;

trait ChecksMailAccess
{
    /**
     * Check if the user is a recipient of the incoming mail.
     */
    private function isRecipientOfMail(User $user, IncomingMail $incomingMail): bool
    {
        return $incomingMail->recipients()
            ->where('recipients.username', $user->username)
            ->exists();
    }

    /**
     * Check if the user is a member of a service linked to the incoming mail.
     */
    private function isMemberOfLinkedService(User $user, IncomingMail $incomingMail): bool
    {
        // Read the links unscoped: a service of another department is still the
        // user's service, and the department scope must not hide it here.
        $serviceIds = $incomingMail->services()->withoutGlobalScopes()->pluck('courrier_services.id');

        if ($serviceIds->isEmpty()) {
            return false;
        }

        return Recipient::query()
            ->where('recipients.username', $user->username)
            ->whereHas('services', fn ($query) => $query->withoutGlobalScopes()->whereIn('courrier_services.id', $serviceIds))
            ->exists();
    }
}
