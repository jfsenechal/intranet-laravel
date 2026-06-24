<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Policies;

use AcMarche\Courrier\Models\Attachment;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use App\Models\User;

final class AttachmentPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user): ?bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can download the attachment.
     *
     * Recipients and linked-service members may download. Department
     * administrators may download mail from their department. Users who only
     * index a department may view the mail but not its attachments.
     */
    public function download(User $user, Attachment $attachment): bool
    {
        $incomingMail = $attachment->incomingMail()->withoutGlobalScopes()->first();

        if ($incomingMail === null) {
            return false;
        }

        if ($this->isRecipientOfMail($user, $incomingMail)) {
            return true;
        }

        if ($this->isMemberOfLinkedService($user, $incomingMail)) {
            return true;
        }

        return $this->administersDepartment($user, $incomingMail);
    }

    /**
     * Check if the user administers the mail's department.
     */
    private function administersDepartment(User $user, IncomingMail $incomingMail): bool
    {
        if ($incomingMail->department === null) {
            return false;
        }

        foreach ($user->getCourrierDepartments() as $department) {
            if ($department->value === $incomingMail->department) {
                return true;
            }
        }

        return false;
    }

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
        $serviceIds = $incomingMail->services()->pluck('courrier_services.id');

        if ($serviceIds->isEmpty()) {
            return false;
        }

        return Recipient::query()
            ->where('recipients.username', $user->username)
            ->whereHas('services', fn ($query) => $query->whereIn('courrier_services.id', $serviceIds))
            ->exists();
    }
}
